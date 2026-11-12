-- =====================================================================
--  Utilisateur MySQL dédié à l'application (principe du moindre privilège)
--  Il peut lire et écrire les données, mais PAS modifier la structure
--  (pas de DROP, ALTER, CREATE) : une injection SQL ne pourrait pas supprimer les tables.
--  Adapter le mot de passe en production.
-- =====================================================================
CREATE USER IF NOT EXISTS 'vite_gourmand'@'%' IDENTIFIED BY 'dev-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vite_gourmand'@'%';
FLUSH PRIVILEGES;
