"""
Tests de bout en bout de l'application (60 vérifications fonctionnelles et de sécurité).

Prérequis : base fraîchement importée (01, 02, 03), `php bin/sync-mongo.php`,
serveur lancé avec `php -S 127.0.0.1:8000 -t public public/index.php`, MAIL_HOST vide.
Lancement : python3 tests/e2e.py   (les requêtes SQL de vérification utilisent `sudo mysql`)
"""
import re, glob, os, json, subprocess, urllib.request, urllib.parse, http.cookiejar, time

BASE = 'http://127.0.0.1:8000'
MAILS = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'storage', 'mails')
ok_count = 0; fails = []

def check(cond, label):
    global ok_count
    if cond: ok_count += 1; print('  OK ', label)
    else: fails.append(label); print('  FAIL', label)

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k): return None

class Client:
    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar), NoRedirect)
    def req(self, path, data=None, files=None):
        body = None; headers = {}
        if files:
            boundary = 'BOUNDARY123'
            parts = []
            for k, v in (data or {}).items():
                for vv in (v if isinstance(v, list) else [v]):
                    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{vv}\r\n'.encode())
            for k, (fname, content, ctype) in files.items():
                parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"; filename="{fname}"\r\nContent-Type: {ctype}\r\n\r\n'.encode() + content + b'\r\n')
            parts.append(f'--{boundary}--\r\n'.encode())
            body = b''.join(parts); headers['Content-Type'] = f'multipart/form-data; boundary={boundary}'
        elif data is not None:
            body = urllib.parse.urlencode(data, doseq=True).encode()
        r = urllib.request.Request(BASE + path, data=body, headers=headers)
        try:
            resp = self.op.open(r); return resp.status, resp.read().decode(), resp.headers.get('Location')
        except urllib.error.HTTPError as e:
            return e.code, e.read().decode(), e.headers.get('Location')
    def get(self, p): return self.req(p)
    def token(self, path):
        _, html, _ = self.get(path)
        m = re.search(r'name="csrf_token" value="([0-9a-f]+)"', html)
        return m.group(1) if m else ''
    def post(self, path, data, form_page=None, files=None):
        data = dict(data); data['csrf_token'] = self.token(form_page or path)
        return self.req(path, data, files)
    def login(self, email, pw):
        return self.post('/connexion', {'email': email, 'password': pw})

def sql(q):
    return subprocess.run(['sudo', 'mysql', '-N', 'vite_gourmand', '-e', q], capture_output=True, text=True).stdout.strip()

def last_mail():
    files = sorted(glob.glob(MAILS + '/*.html'), key=os.path.getmtime)
    return open(files[-1]).read() if files else ''

print('1. Inscription')
c = Client()
s, html, _ = c.post('/inscription', {'prenom': 'Léa', 'nom': 'Bernard', 'email': 'lea@test.fr', 'telephone': '06 11 22 33 44',
    'adresse_postale': '1 rue X', 'code_postal': '33000', 'ville': 'Bordeaux', 'password': 'faible', 'password_confirmation': 'faible', 'rgpd': '1'})
check(s == 200 and '10 caractères' in html, 'mot de passe faible refusé')
s, html, loc = c.post('/inscription', {'prenom': '<script>alert(1)</script>', 'nom': 'Bernard', 'email': 'lea@test.fr', 'telephone': '06 11 22 33 44',
    'adresse_postale': '1 rue X', 'code_postal': '33000', 'ville': 'Bordeaux', 'password': 'Solide#Pass2026', 'password_confirmation': 'Solide#Pass2026', 'rgpd': '1'})
check(s == 302 and loc.endswith('/espace'), 'inscription valide -> espace')
check(sql("SELECT r.libelle FROM utilisateur u JOIN role r USING(role_id) WHERE email='lea@test.fr'") == 'utilisateur', 'rôle utilisateur imposé')
check(sql("SELECT password FROM utilisateur WHERE email='lea@test.fr'").startswith('$2y$12$'), 'mot de passe haché bcrypt')
check('Bienvenue' in last_mail(), 'mail de bienvenue envoyé')
s, html, _ = c.get('/espace')
check('<script>alert(1)</script>' not in html and '&lt;script&gt;' in html, 'XSS échappé à l\'affichage')

print('2. Connexion et sécurité')
c2 = Client()
s, html, _ = c2.login('marie.dupont@example.com', 'mauvais')
check('Identifiants incorrects' in html, 'mauvais mot de passe refusé (message générique)')
s, html, loc = c2.login('ancien.employe@vite-gourmand.fr', 'Employe@Vite2026!')
check('désactivé' in html, 'compte désactivé refusé')
c3 = Client()
for i in range(5): c3.login('thomas.martin@example.com', 'faux')
s, html, _ = c3.login('thomas.martin@example.com', 'Client@Vite2026!')
check('Trop de tentatives' in html, 'blocage après 5 échecs (anti brute-force)')
sql("DELETE FROM tentative_connexion")
s, html, loc = c2.login('marie.dupont@example.com', 'Client@Vite2026!')
check(s == 302 and loc.endswith('/espace'), 'connexion client OK')
s, _, loc = Client().req('/connexion', {'email': 'x', 'password': 'y'})
check(s == 400, 'POST sans jeton CSRF refusé (400)')
s, _, loc = c2.post('/connexion', {'email': 'marie.dupont@example.com', 'password': 'Client@Vite2026!', 'redirect': 'https://pirate.com'}, form_page='/connexion')
c4 = Client(); s, _, loc = c4.post('/connexion', {'email': 'marie.dupont@example.com', 'password': 'Client@Vite2026!', 'redirect': '//pirate.com'})
check(loc and 'pirate' not in loc, 'open redirect bloqué')

print('3. Contrôle d\'accès')
s, _, _ = c2.get('/employe'); check(s == 403, 'client -> espace employé interdit (403)')
s, _, _ = c2.get('/espace/commande?id=4'); check(s == 404, 'client ne voit pas la commande d\'un autre (IDOR)')
s, _, _ = c2.get('/api/statistiques'); check(s == 403, 'client -> API stats interdite')

print('4. Commande')
stock = int(sql('SELECT quantite_restante FROM menu WHERE menu_id=3'))
demain = time.strftime('%Y-%m-%d', time.localtime(time.time() + 86400 * 10))
s, html, _ = c2.post('/commander', {'menu_id': '3', 'date_prestation': demain, 'heure_livraison': '12:30', 'adresse_prestation': '8 cours de l\'Intendance',
    'code_postal': '33000', 'ville_prestation': 'Bordeaux', 'nombre_personne': '5'}, form_page='/commander?menu=3')
check('6 personnes minimum' in html, 'nombre de personnes < minimum refusé')
s, html, loc = c2.post('/commander', {'menu_id': '3', 'date_prestation': demain, 'heure_livraison': '12:30', 'adresse_prestation': '8 cours de l\'Intendance',
    'code_postal': '33000', 'ville_prestation': 'Bordeaux', 'nombre_personne': '11'}, form_page='/commander?menu=3')
check(s == 302 and '/espace/commande?id=' in (loc or ''), 'commande créée')
cid = re.search(r'id=(\d+)', loc).group(1)
check(sql(f'SELECT prix_menu, montant_reduction, prix_livraison, prix_total FROM commande WHERE commande_id={cid}') == '330.00\t33.00\t0.00\t297.00', 'prix serveur: 330 - 10% = 297, livraison 0 à Bordeaux')
check(int(sql('SELECT quantite_restante FROM menu WHERE menu_id=3')) == stock - 1, 'stock décrémenté')
check('Confirmation' in last_mail() or 'bien reçu' in last_mail(), 'mail de confirmation')
check(sql(f"SELECT numero_commande FROM commande WHERE commande_id={cid}").startswith('CMD-'), 'numéro de commande généré')
# Livraison hors Bordeaux (API Adresse injoignable dans ce bac à sable -> coordonnées du navigateur)
s, html, loc = c2.post('/commander', {'menu_id': '4', 'date_prestation': demain, 'heure_livraison': '19:00', 'adresse_prestation': '3 avenue de l\'Yser',
    'code_postal': '33700', 'ville_prestation': 'Mérignac', 'nombre_personne': '4', 'latitude': '44.8386', 'longitude': '-0.6436'}, form_page='/commander?menu=4')
cid2 = re.search(r'id=(\d+)', loc or 'id=0').group(1)
row = sql(f'SELECT distance_km, prix_livraison, prix_total FROM commande WHERE commande_id={cid2}')
print('    Mérignac ->', row)
check(row.startswith('6.00\t8.54'), 'livraison hors Bordeaux: 5 + 0,59 x 6 km = 8,54 €')

print('5. Modification / annulation client')
s, html, loc = c2.post('/espace/commande/modifier', {'id': cid, 'date_prestation': demain, 'heure_livraison': '13:00', 'adresse_prestation': '8 cours de l\'Intendance',
    'code_postal': '33000', 'ville_prestation': 'Bordeaux', 'nombre_personne': '6', 'menu_id': '1'}, form_page=f'/espace/commande/modifier?id={cid}')
check(sql(f'SELECT menu_id, nombre_personne, prix_total FROM commande WHERE commande_id={cid}') == '3\t6\t180.00', 'modification: prix recalculé, menu inchangé')
s, _, _ = c2.post('/espace/commande', {'id': cid, 'action': 'annuler'}, form_page=f'/espace/commande?id={cid}')
check(sql(f'SELECT statut, annulee_par FROM commande WHERE commande_id={cid}') == 'annulee\tclient', 'annulation client')
check(int(sql('SELECT quantite_restante FROM menu WHERE menu_id=3')) == stock, 'stock restitué')
s, html, _ = c2.post('/espace/commande', {'id': '2', 'action': 'annuler'}, form_page='/espace/commande?id=2')
check(sql('SELECT statut FROM commande WHERE commande_id=2') == 'accepte', 'commande acceptée non annulable par le client')

print('6. Employé')
e = Client(); s, _, loc = e.login('julie@vite-gourmand.fr', 'Employe@Vite2026!')
check(loc.endswith('/employe'), 'connexion employée -> /employe')
s, html, _ = e.get('/employe?statut=en_attente'); check('CMD-2026-00003' in html and 'CMD-2026-00002' not in html, 'filtre par statut')
s, html, _ = e.get('/employe?client=thomas'); check('CMD-2026-00004' in html and 'CMD-2026-00003' not in html, 'filtre par client')
s, _, _ = e.get('/admin/employes'); check(s == 403, 'employée -> espace admin interdit')
e.post('/employe/commande', {'id': '2', 'action': 'statut', 'statut': 'terminee'}, form_page='/employe/commande?id=2')
check(sql('SELECT statut FROM commande WHERE commande_id=2') == 'accepte', 'transition interdite refusée (accepté -> terminée)')
e.post('/employe/commande', {'id': '2', 'action': 'statut', 'statut': 'en_preparation'}, form_page='/employe/commande?id=2')
check(sql('SELECT statut FROM commande WHERE commande_id=2') == 'en_preparation', 'statut -> en préparation')
check(sql("SELECT COUNT(*) FROM suivi_commande WHERE commande_id=2 AND statut='en_preparation'") == '1', 'historique de suivi enregistré')
s, html, _ = e.post('/employe/commande', {'id': '3', 'action': 'annuler', 'motif': ''}, form_page='/employe/commande?id=3')
check(sql('SELECT statut FROM commande WHERE commande_id=3') == 'en_attente', 'annulation sans contact ni motif refusée')
e.post('/employe/commande', {'id': '3', 'action': 'annuler', 'motif': 'Rupture produit', 'mode_contact': 'telephone', 'contact_ok': '1'}, form_page='/employe/commande?id=3')
check(sql('SELECT statut, mode_contact FROM commande WHERE commande_id=3') == 'annulee\ttelephone', 'annulation employé avec motif + contact')
e.post('/employe/commande', {'id': '4', 'action': 'statut', 'statut': 'terminee'}, form_page='/employe/commande?id=4')
check(sql('SELECT statut FROM commande WHERE commande_id=4') == 'terminee' and 'avis' in last_mail(), 'matériel rendu -> terminée + mail demande d\'avis')
check(sql('SELECT restitution_materiel FROM commande WHERE commande_id=4') == '1', 'restitution_materiel passe à vrai (MCD)')
check(sql("SELECT pays FROM utilisateur WHERE email='lea@test.fr'") == 'France', 'pays par défaut enregistré (MCD)')
for st in ['en_cours_de_livraison', 'livre', 'terminee']:
    e.post('/employe/commande', {'id': '2', 'action': 'statut', 'statut': st}, form_page='/employe/commande?id=2')
check(sql('SELECT statut FROM commande WHERE commande_id=2') == 'terminee', 'livrée sans matériel -> terminée')
s, html, _ = e.post('/employe/horaires', {'horaires[1][ferme]': '1', **{f'horaires[{i}][ouverture]': '09:00' for i in range(2, 8)}, **{f'horaires[{i}][fermeture]': '18:00' for i in range(2, 8)}}, form_page='/employe/horaires')
check(sql("SELECT heure_fermeture FROM horaire WHERE jour='dimanche'") == '18:00:00', 'horaires modifiés')

print('7. Avis')
s, _, _ = c2.post('/espace/avis', {'commande_id': '2', 'note': '7', 'commentaire': 'Top'}, form_page='/espace/commande?id=2')
check(sql('SELECT COUNT(*) FROM avis WHERE commande_id=2') == '0', 'note hors 1-5 refusée')
c2.post('/espace/avis', {'commande_id': '2', 'note': '5', 'commentaire': 'Entrecôte parfaite !'}, form_page='/espace/commande?id=2')
check(sql('SELECT statut FROM avis WHERE commande_id=2') == 'en_attente', 'avis déposé en attente de modération')
s, html, _ = Client().get('/'); check('Entrecôte parfaite' not in html, 'avis non validé invisible sur l\'accueil')
aid = sql('SELECT avis_id FROM avis WHERE commande_id=2')
e.post('/employe/avis', {'avis_id': aid, 'decision': 'valide'}, form_page='/employe/avis')
s, html, _ = Client().get('/'); check('Entrecôte parfaite' in html, 'avis validé visible sur l\'accueil')

print('8. Menus (CRUD + upload)')
png = open(os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'public/assets/img/menus/vegan-1.webp'), 'rb').read()
s, html, loc = e.post('/employe/menu', {'action': 'enregistrer', 'titre': 'Menu test', 'description': 'Desc', 'theme_id': '3', 'regime_id': '2',
    'nombre_personne_minimum': '2', 'prix_par_personne': '25', 'conditions': '48h avant', 'quantite_restante': '3', 'plats[]': ['2', '7'], 'texte_alt': 'Un bol'},
    form_page='/employe/menu', files={'image': ('photo.webp', png, 'image/webp')})
mid = sql("SELECT menu_id FROM menu WHERE titre='Menu test'")
check(mid != '' and sql(f'SELECT COUNT(*) FROM menu_plat WHERE menu_id={mid}') == '2', 'menu créé avec 2 plats')
check(sql(f'SELECT chemin FROM menu_image WHERE menu_id={mid}').startswith('assets/img/menus/uploads/'), 'image envoyée et renommée')
s, html, _ = e.post('/employe/menu', {'action': 'enregistrer', 'menu_id': mid, 'titre': 'Menu test', 'description': 'Desc', 'theme_id': '3', 'regime_id': '2',
    'nombre_personne_minimum': '2', 'prix_par_personne': '25', 'conditions': '48h', 'quantite_restante': '3', 'plats[]': ['2'], 'texte_alt': 'x'},
    form_page=f'/employe/menu?id={mid}', files={'image': ('shell.php.webp', b'<?php system($_GET["c"]); ?>', 'image/webp')})
check('Format refusé' in html, 'faux fichier image (PHP) refusé')
s, html, _ = Client().get('/api/menus?prix_max=60'); check('Menu test' in html and '"prix":50' in html, 'nouveau menu visible dans la vue globale')
e.post('/employe/menus', {'action': 'supprimer', 'menu_id': mid}, form_page='/employe/menus')
check(sql(f'SELECT COUNT(*) FROM menu WHERE menu_id={mid}') == '0', 'menu sans commande supprimé')
e.post('/employe/menus', {'action': 'supprimer', 'menu_id': '5'}, form_page='/employe/menus')
check(sql('SELECT actif FROM menu WHERE menu_id=5') == '0', 'menu déjà commandé archivé (pas supprimé)')
e.post('/employe/menus', {'action': 'reactiver', 'menu_id': '5'}, form_page='/employe/menus')
s, _, _ = e.post('/employe/plats', {'action': 'enregistrer', 'plat_id': '0', 'titre_plat': 'Soupe test', 'type_plat': 'entree', 'allergenes[]': ['9', '7']}, form_page='/employe/plats')
check(sql("SELECT COUNT(*) FROM plat_allergene pa JOIN plat p USING(plat_id) WHERE p.titre_plat='Soupe test'") == '2', 'plat créé avec allergènes')

print('9. Administrateur')
a = Client(); a.login('jose@vite-gourmand.fr', 'Admin@Vite2026!')
s, _, _ = a.get('/employe'); check(s == 200, 'admin accède à l\'espace employé')
a.post('/admin/employes', {'action': 'creer', 'prenom': 'Nora', 'nom': 'Petit', 'email': 'nora@vite-gourmand.fr', 'password': 'Nora#Cuisine26', 'password_confirmation': 'Nora#Cuisine26'}, form_page='/admin/employes')
check(sql("SELECT r.libelle FROM utilisateur JOIN role r USING(role_id) WHERE email='nora@vite-gourmand.fr'") == 'employe', 'compte employé créé')
m = last_mail(); check('compte' in m and 'Nora#Cuisine26' not in m, 'mail employé SANS le mot de passe')
nid = sql("SELECT utilisateur_id FROM utilisateur WHERE email='nora@vite-gourmand.fr'")
n = Client(); n.login('nora@vite-gourmand.fr', 'Nora#Cuisine26')
a.post('/admin/employes', {'action': 'desactiver', 'utilisateur_id': nid}, form_page='/admin/employes')
s, _, loc = n.get('/employe'); check(s == 302, 'session de l\'employé désactivé coupée immédiatement')
a.post('/admin/employes', {'action': 'desactiver', 'utilisateur_id': '1'}, form_page='/admin/employes')
check(sql('SELECT actif FROM utilisateur WHERE utilisateur_id=1') == '1', 'impossible de désactiver l\'administrateur')
s, body, _ = a.get('/api/statistiques'); data = json.loads(body)
print('    stats:', [(x['menu_titre'], x['nb_commandes'], x['chiffre_affaires']) for x in data['menus']])
check(s == 200 and data['total_commandes'] > 0, 'statistiques MongoDB')
real = sql("SELECT COUNT(*), SUM(prix_total) FROM commande WHERE statut <> 'annulee'").split('\t')
check(data['total_commandes'] == int(real[0]) and abs(data['chiffre_affaires'] - float(real[1])) < 0.01, 'MongoDB synchronisé avec MySQL (nb et CA identiques)')
s, body, _ = a.get('/api/statistiques?menu=3&debut=2026-06-01&fin=2026-06-30'); d = json.loads(body)
check(d['total_commandes'] == 1 and d['chiffre_affaires'] == 297.0, 'filtre menu + période')

print('10. Mot de passe oublié')
r = Client(); r.post('/mot-de-passe-oublie', {'email': 'thomas.martin@example.com'})
lien = re.search(r'href="(http[^"]+reinitialiser\?token=[0-9a-f]+)"', last_mail()).group(1)
path = lien.replace('http://localhost:8000', '')
r.post(path.split('?')[0], {'token': path.split('=')[1], 'password': 'Nouveau#Pass2026', 'password_confirmation': 'Nouveau#Pass2026'}, form_page=path)
s, _, loc = Client().login('thomas.martin@example.com', 'Nouveau#Pass2026'); check(s == 302 and loc.endswith('/espace'), 'réinitialisation puis connexion OK')
s, _, loc = r.get(path); check(s == 302, 'lien de réinitialisation à usage unique')

print('11. Contact')
s, html, _ = Client().post('/contact', {'titre': 'Devis mariage', 'message': '120 personnes', 'email': 'visiteur@test.fr'})
check('bien été envoyé' in html and 'Devis mariage' in last_mail(), 'demande de contact envoyée à l\'entreprise')

print(f'\n{ok_count} OK, {len(fails)} échec(s)')
for f in fails: print('  -', f)
