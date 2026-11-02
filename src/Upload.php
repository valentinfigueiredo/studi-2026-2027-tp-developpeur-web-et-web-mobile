<?php

declare(strict_types=1);

namespace App;

/**
 * Envoi sécurisé des images de menus.
 *
 * Risques couverts :
 *  - fichier PHP déguisé en image : on vérifie le VRAI type du contenu (finfo + getimagesize),
 *    pas l'extension ni le type annoncé par le navigateur (facilement falsifiables) ;
 *  - écrasement / parcours de dossiers : le fichier est renommé avec un nom aléatoire ;
 *  - saturation du disque : taille limitée à 2 Mo.
 */
final class Upload
{
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const TAILLE_MAX = 2 * 1024 * 1024;
    public const DOSSIER = 'assets/img/menus/uploads';

    /** @return string chemin relatif à public/ ; lève une exception si le fichier est refusé */
    public static function image(array $fichier): string
    {
        if (($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \DomainException('L\'envoi de l\'image a échoué.');
        }
        if ($fichier['size'] > self::TAILLE_MAX) {
            throw new \DomainException('L\'image ne doit pas dépasser 2 Mo.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
        if (!isset(self::TYPES[$mime]) || getimagesize($fichier['tmp_name']) === false) {
            throw new \DomainException('Format refusé : seules les images JPG, PNG et WebP sont acceptées.');
        }

        $dossier = dirname(__DIR__) . '/public/' . self::DOSSIER;
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }
        $nom = bin2hex(random_bytes(16)) . '.' . self::TYPES[$mime];
        if (!move_uploaded_file($fichier['tmp_name'], $dossier . '/' . $nom)) {
            throw new \RuntimeException('Impossible d\'enregistrer l\'image.');
        }
        return self::DOSSIER . '/' . $nom;
    }

    public static function supprimer(string $chemin): void
    {
        // On ne supprime que des fichiers du dossier d'upload (jamais un autre fichier du serveur)
        if (str_starts_with($chemin, self::DOSSIER . '/') && !str_contains($chemin, '..')) {
            @unlink(dirname(__DIR__) . '/public/' . $chemin);
        }
    }
}
