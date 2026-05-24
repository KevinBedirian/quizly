-- SQL Dump Complet pour Quizly
-- Structure + 100 Questions initiales

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. STRUCTURE DES TABLES
-- --------------------------------------------------------

-- Table des catégories
DROP TABLE IF EXISTS `reponses`;
DROP TABLE IF EXISTS `tentatives`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des utilisateurs
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: utilisateur, 1: admin',
  `moyenne_generale` decimal(4,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des questions
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_categorie` int(11) NOT NULL,
  `intitule` text NOT NULL,
  `proposition_a` varchar(255) NOT NULL,
  `proposition_b` varchar(255) NOT NULL,
  `proposition_c` varchar(255) NOT NULL,
  `proposition_d` varchar(255) NOT NULL,
  `reponse` char(1) NOT NULL COMMENT 'A, B, C ou D',
  `difficulte` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:Facile, 2:Moyen, 3:Difficile',
  PRIMARY KEY (`id`),
  KEY `fk_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des scores/tentatives
CREATE TABLE `tentatives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int(11) NOT NULL,
  `score` float NOT NULL,
  `date_passage` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motif` varchar(255) DEFAULT NULL COMMENT 'Motif du 0 en cas de triche (ex: TRICHE - Sortie fullscreen)',
  PRIMARY KEY (`id`),
  KEY `fk_user` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table du détail des réponses (pour historique)
CREATE TABLE `reponses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tentative_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `reponse_utilisateur` char(1) NOT NULL,
  `correcte` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tentative` (`tentative_id`),
  KEY `fk_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. INSERTION DES DONNÉES DE BASE
-- --------------------------------------------------------

-- Insertion des catégories
INSERT INTO `categories` (`id`, `nom`) VALUES
(1, 'Cybersécurité'),
(2, 'Développement Web'),
(3, 'Bases de données');

-- Insertion d'un admin par défaut (mdp: Admin1234!)
INSERT INTO `users` (`nom`, `prenom`, `email`, `mdp`, `role`) VALUES
('Admin', 'Quizly', 'admin@quizly.fr', '$2y$10$4NGjyiYuvgZdXvDe/iOTxeTszDLT5YiF3BiROMbIpsQGr96qcpva.', 1);

-- --------------------------------------------------------
-- 3. INSERTION DES 100 QUESTIONS (VERSION CORRIGÉE)
-- --------------------------------------------------------

INSERT INTO `questions` (`id_categorie`, `intitule`, `proposition_a`, `proposition_b`, `proposition_c`, `proposition_d`, `reponse`, `difficulte`) VALUES
-- CYBERSECURITE (1 à 34)
(1, 'Que signifie l''acronyme VPN ?', 'Virtual Private Network', 'Very Personal Network', 'Verified Port Node', 'Visual Protocol Network', 'A', 1),
(1, 'Quel type d''attaque sature un serveur de requêtes ?', 'Phishing', 'DDoS', 'Ransomware', 'SQL Injection', 'B', 2),
(1, 'Quel protocole sécurise les transferts de pages web ?', 'HTTP', 'FTP', 'HTTPS', 'SSH', 'C', 1),
(1, 'Qu''est-ce que le salage (salting) en cryptographie ?', 'Ajouter des caractères aux fichiers', 'Une méthode de hachage rapide', 'Ajouter des données aléatoires avant hachage', 'Une technique de phishing', 'C', 3),
(1, 'Quelle est la fonction d''un pare-feu (Firewall) ?', 'Accélérer la connexion', 'Filtrer le trafic réseau', 'Nettoyer le disque dur', 'Éteindre l''ordinateur', 'B', 1),
(1, 'Que signifie l''acronyme 2FA ?', 'Double accès fichier', 'Authentification à deux facteurs', 'Second Pare-feu', 'Archive Double', 'B', 1),
(1, 'Une attaque ''Man-in-the-middle'' consiste à...', 'Intercepter les communications', 'Casser un mot de passe par force brute', 'Envoyer des spams', 'Voter deux fois', 'A', 2),
(1, 'Quel port est utilisé par défaut pour le protocole SSH ?', '80', '443', '21', '22', 'D', 2),
(1, 'Qu''est-ce qu''un Rootkit ?', 'Un kit de réparation', 'Un logiciel malveillant caché', 'Un type de processeur', 'Un outil de gestion de base de données', 'B', 3),
(1, 'Le protocole TLS succède à quel protocole ?', 'SSL', 'UDP', 'IMAP', 'POP3', 'A', 2),
(1, 'Quelle est la principale faiblesse du protocole WEP ?', 'Il est trop lent', 'Sa clé est facilement cassable', 'Il ne fonctionne pas sur mobile', 'Il consomme trop d''énergie', 'B', 2),
(1, 'Qu''est-ce qu''un ''Honeypot'' ?', 'Un serveur de sauvegarde', 'Un système appât pour les pirates', 'Un gestionnaire de mots de passe', 'Un virus très lent', 'B', 3),
(1, 'En cyber, que signifie CIA ?', 'Central Intelligence Agency', 'Confidentiality, Integrity, Availability', 'Control, Identity, Access', 'Code, Input, Analysis', 'B', 2),
(1, 'Quel est le but du Phishing ?', 'Récupérer des données via mail/SMS', 'Détruire le matériel informatique', 'Améliorer le SEO', 'Miner de la crypto', 'A', 1),
(1, 'Le RGPD concerne...', 'Le prix du matériel', 'La protection des données personnelles', 'Le débit internet', 'La création de sites', 'B', 1),
(1, 'Technique testant toutes les combinaisons de mots de passe ?', 'Injection SQL', 'Social Engineering', 'Brute Force', 'XSS', 'C', 1),
(1, 'Qu''est-ce que le ''Zero-Day'' ?', 'Une faille sans correctif connu', 'Un jour sans attaques', 'Une erreur de débutant', 'Un mot de passe à 0 chiffres', 'A', 2),
(1, 'Quel algorithme est considéré comme obsolète ?', 'AES-256', 'RSA', 'MD5', 'SHA-256', 'C', 2),
(1, 'Que permet le protocole WPA3 ?', 'Vidéos HD', 'Sécuriser les réseaux Wi-Fi', 'Accélérer les downloads', 'Mails chiffrés', 'B', 2),
(1, 'Qu''est-ce que l''ingénierie sociale ?', 'Codage social', 'Manipulation psychologique', 'Calcul de stats', 'Partage Facebook', 'B', 1),
(1, 'Outil permettant de scanner les ports d''une machine ?', 'Wireshark', 'Nmap', 'Metasploit', 'VLC', 'B', 2),
(1, 'Un Ransomware a pour but de...', 'Chiffrer vos données contre rançon', 'Supprimer vos photos', 'Afficher des pubs', 'Vider votre batterie', 'A', 1),
(1, 'Le protocole DNSSEC sert à...', 'Accélérer le web', 'Sécuriser les réponses DNS', 'Bloquer les sites', 'Chiffrer les disques', 'B', 3),
(1, 'Longueur minimale recommandée d''une clé AES ?', '8 bits', '32 bits', '128 bits', '1024 bits', 'C', 2),
(1, 'Un ''Botnet'' est un réseau de...', 'Robots de cuisine', 'Ordinateurs zombies', 'Serveurs Google', 'Joueurs en ligne', 'B', 2),
(1, 'Que signifie IDS ?', 'Intrusion Detection System', 'Internal Data Storage', 'Input Device Software', 'Internet Dark Side', 'A', 2),
(1, 'Le ''Sniffing'' consiste à...', 'Sentir le matériel', 'Écouter le trafic réseau', 'Deviner un mot de passe', 'Vendre des données', 'B', 2),
(1, 'Risque d''une injection SQL ?', 'Modifier le CSS', 'Accéder/modifier la BDD', 'Éteindre le serveur', 'Changer l''URL', 'B', 2),
(1, 'Qu''est-ce que le XSS ?', 'Format image', 'Exécution de script côté client', 'Processeur Intel', 'Serveur proxy', 'B', 2),
(1, 'Quelle entité a créé Kerberos ?', 'Microsoft', 'MIT', 'Apple', 'Google', 'B', 3),
(1, 'Rôle de la commande ''shred'' sous Linux ?', 'Afficher un fichier', 'Effacer définitivement un fichier', 'Compresser', 'Renommer', 'B', 3),
(1, 'Rôle d''un certificat SSL/TLS ?', 'Images', 'Identité du site et chiffrement', 'Validité email', 'Stopper virus', 'B', 1),
(1, 'Attaque utilisant des hachages pré-calculés ?', 'Rainbow Tables', 'Dictionnaire', 'Phishing', 'Replay Attack', 'A', 3),
(1, 'Le BYOD (Bring Your Own Device) est un risque de...', 'Électricité', 'Sécurité des données entreprise', 'Poids du sac', 'Lenteur réseau', 'B', 1),

-- DÉVELOPPEMENT WEB (35 à 67)
(2, 'Que signifie CSS ?', 'Cascading Style Sheets', 'Creative Style Sheets', 'Computer Style Sheets', 'Complex Style Selection', 'A', 1),
(2, 'Quelle balise HTML permet d''insérer du JavaScript ?', '<js>', '<javascript>', '<script>', '<code>', 'C', 1),
(2, 'Variable PHP contenant les données POST ?', '$_GET', '$_SESSION', '$_POST', '$_DATA', 'C', 1),
(2, 'Framework basé sur JavaScript ?', 'Laravel', 'Django', 'React', 'Ruby on Rails', 'C', 1),
(2, 'Effet de l''attribut target=''_blank'' sur un lien ?', 'Couleur', 'Nouvel onglet', 'Download', 'Souligné', 'B', 1),
(2, 'Propriété CSS changeant la police ?', 'font-family', 'text-style', 'font-weight', 'type-face', 'A', 1),
(2, 'Comment déclarer une variable en JS ?', 'let', 'var', 'const', 'Toutes ces réponses', 'D', 1),
(2, 'Équivalent de l''id pour plusieurs éléments ?', 'class', 'group', 'tags', 'selector', 'A', 1),
(2, 'Remplaçant de ''float: left'' aujourd''hui ?', 'Grid ou Flexbox', 'Position: fixed', 'Display: table', 'Margin: auto', 'A', 2),
(2, 'Méthode JS transformant texte en objet JSON ?', 'JSON.stringify()', 'JSON.parse()', 'JSON.toObject()', 'Object.json()', 'B', 2),
(2, 'Concaténer deux chaînes en PHP ?', 'Avec +', 'Avec &', 'Avec .', 'Avec ,', 'C', 1),
(2, 'Code HTTP ''Non trouvé'' ?', '200', '500', '403', '404', 'D', 1),
(2, 'Rôle de l''attribut ''alt'' d''une image ?', 'Alignement', 'Texte alternatif', 'Altitude', 'Ancre', 'B', 1),
(2, 'Comment centrer en Flexbox ?', 'align-items/justify-content: center', 'text-align: center', 'margin: auto', 'position: absolute', 'A', 2),
(2, 'Hook React gérant l''état ?', 'useEffect', 'useContext', 'useState', 'useReducer', 'C', 2),
(2, 'Langage exécuté côté serveur ?', 'HTML', 'CSS', 'PHP', 'JS (standard)', 'C', 1),
(2, 'Que signifie API ?', 'Application Programming Interface', 'Advanced Program Integration', 'Automated Protocol Internet', 'Access Point Information', 'A', 2),
(2, 'Unité CSS dépendant de la police parente ?', 'px', 'em', 'rem', 'vh', 'B', 2),
(2, 'Commande Git envoyant les fichiers au dépôt ?', 'git pull', 'git commit', 'git push', 'git add', 'C', 1),
(2, 'Effet de array_pop() en PHP ?', 'Ajoute fin', 'Supprime dernier élément', 'Trie', 'Compte', 'B', 2),
(2, 'Résultat de 3 + ''3'' en JS ?', '6', '33', 'undefined', 'Error', 'B', 2),
(2, 'Sélecteur CSS ciblant <p> dans un <div> ?', 'div p', 'div > p', 'div.p', 'div + p', 'A', 1),
(2, 'Signification du ''S'' dans HTTPS ?', 'Secret', 'Secure', 'Static', 'System', 'B', 1),
(2, 'Outil gérant les dépendances PHP ?', 'npm', 'pip', 'Composer', 'Maven', 'C', 2),
(2, 'Balise HTML pour le titre de l''onglet ?', '<head>', '<h1>', '<title>', '<meta>', 'C', 1),
(2, 'Rôle de Webpack ?', 'Hébergement', 'Bundler d''assets/scripts', 'Tests', 'BDD', 'B', 3),
(2, 'Que signifie DOM ?', 'Data Object Model', 'Document Object Model', 'Digital Online Module', 'Direct Object Mapping', 'B', 2),
(2, 'Mot-clé JS attendant une promesse ?', 'wait', 'hold', 'async', 'await', 'D', 2),
(2, 'Balise contenant les métadonnées ?', '<body>', '<footer>', '<head>', '<section>', 'C', 1),
(2, 'Site s''adaptant aux mobiles ?', 'Adaptative', 'Responsive', 'Mobile-First', 'Flexible', 'B', 1),
(2, 'Rôle de ''z-index'' en CSS ?', 'Transparence', 'Profondeur (empilement)', 'Zoom', 'Largeur', 'B', 2),
(2, 'Lequel n''est pas un préprocesseur CSS ?', 'Sass', 'Less', 'Stylus', 'Pug', 'D', 3),
(2, 'Fonction PHP hachant un mot de passe ?', 'md5()', 'sha1()', 'password_hash()', 'encrypt()', 'C', 2),

-- BASES DE DONNÉES (68 à 100)
(3, 'Que signifie SQL ?', 'Simple Query Language', 'Structured Query Language', 'Standard Quality List', 'Secure Query Link', 'B', 1),
(3, 'Commande supprimant données sans la table ?', 'DELETE', 'DROP', 'TRUNCATE', 'REMOVE', 'C', 2),
(3, 'Qu''est-ce qu''une clé primaire ?', 'Clé physique', 'Identifiant unique', 'Mot de passe', 'Colonne vide', 'B', 1),
(3, 'Clause SQL permettant de trier ?', 'SORT BY', 'GROUP BY', 'ORDER BY', 'ARRANGE', 'C', 1),
(3, 'Commande de mise à jour des données ?', 'INSERT', 'SAVE', 'MODIFY', 'UPDATE', 'D', 1),
(3, 'Signification de ACID ?', 'Atomicity, Consistency, Isolation, Durability', 'Access, Control, Index, Data', 'Auto, Check, Input, Delete', 'Active, Core, Internal, Direct', 'A', 3),
(3, 'Jointure retournant tout de la table gauche ?', 'INNER JOIN', 'RIGHT JOIN', 'LEFT JOIN', 'FULL JOIN', 'C', 2),
(3, 'Effet de DISTINCT ?', 'Trie', 'Supprime les doublons', 'Compte', 'Groupe', 'B', 1),
(3, 'Type de données pour Vrai/Faux ?', 'VARCHAR', 'INT', 'BOOLEAN', 'BLOB', 'C', 1),
(3, 'Compter le nombre d''entrées ?', 'COUNT(*)', 'SUM(*)', 'TOTAL(*)', 'NUMBER(*)', 'A', 1),
(3, 'Qu''est-ce qu''une clé étrangère ?', 'Clé étrangère', 'Lien vers clé primaire externe', 'Index', 'Clé cryptée', 'B', 2),
(3, 'Opérateur recherchant un modèle ?', 'LIKE', 'MATCH', 'SEARCH', 'FIND', 'A', 1),
(3, 'Signification de NoSQL ?', 'No SQL', 'Not Only SQL', 'New SQL', 'Network SQL', 'B', 2),
(3, 'BDD orientée documents ?', 'MySQL', 'PostgreSQL', 'MongoDB', 'Oracle', 'C', 2),
(3, 'Rôle de GROUP BY ?', 'Trie colonnes', 'Regroupe lignes identiques', 'Nouvelle table', 'Supprime', 'B', 2),
(3, 'Supprimer une table entière ?', 'DELETE TABLE', 'DROP TABLE', 'REMOVE TABLE', 'ERASE TABLE', 'B', 1),
(3, 'Fonction SQL de moyenne ?', 'AVG()', 'MEAN()', 'SUM()/COUNT()', 'MEDIUM()', 'A', 2),
(3, 'Utilité d''un index ?', 'Sécurité', 'Accélérer la lecture', 'Espace disque', 'Trier dossiers', 'B', 2),
(3, 'Capacité d''un type TINYINT ?', '10', '255 (non signé)', '65535', 'Illimité', 'B', 3),
(3, 'Ligne dans une BDD relationnelle ?', 'Attribut', 'Tuple/Enregistrement', 'Champ', 'Relation', 'B', 3),
(3, 'Que signifie CRUD ?', 'Create, Read, Update, Delete', 'Copy, Run, Undo, Destroy', 'Create, Restore, Use, Deploy', 'Control, Read, Upload, Disconnect', 'A', 1),
(3, 'Contrainte empêchant valeur nulle ?', 'NOT NULL', 'UNIQUE', 'PRIMARY', 'CHECK', 'A', 1),
(3, 'Format JSON en natif (MySQL/Postgres) ?', 'Non', 'Oui', 'Seulement lecture', 'Via PHP', 'B', 2),
(3, 'Rôle du COMMIT ?', 'Annule', 'Valide la transaction', 'Ferme', 'Mail', 'B', 3),
(3, 'Rôle du ROLLBACK ?', 'Valide', 'Annule la transaction', 'Relance', 'Supprime logs', 'B', 3),
(3, 'Lequel n''est pas un SGBD ?', 'MariaDB', 'Redis', 'Python', 'SQLite', 'C', 1),
(3, 'Signification de SGBD ?', 'Système de Gestion de BDD', 'Service Backup Digital', 'Saisie Groupée', 'Software General Base', 'A', 1),
(3, 'Clause filtrant un agrégat ?', 'WHERE', 'HAVING', 'FILTER', 'LIMIT', 'B', 3),
(3, 'Différence CHAR vs VARCHAR ?', 'CHAR plus long', 'CHAR fixe, VARCHAR variable', 'VARCHAR vieux', 'Aucune', 'B', 3),
(3, 'Mot-clé ajoutant une colonne ?', 'ADD COLUMN', 'UPDATE TABLE', 'ALTER TABLE', 'INSERT INTO', 'C', 2),
(3, 'Un ''Trigger'' est...', 'Erreur fatale', 'Script auto sur événement', 'Bouton', 'Clé secrète', 'B', 3),
(3, 'Commande créant une vue ?', 'NEW VIEW', 'CREATE VIEW', 'ADD VIEW', 'SELECT VIEW', 'B', 2),
(3, 'En SQL, 1 != 2 est...', 'Vrai', 'Faux', 'Erreur', 'NULL', 'A', 1);

-- --------------------------------------------------------
-- 4. CONTRAINTES ET RELATIONS (CLEFS ETRANGERES)
-- --------------------------------------------------------

ALTER TABLE `questions`
  ADD CONSTRAINT `fk_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

ALTER TABLE `tentatives`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`utilisateur_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `reponses`
  ADD CONSTRAINT `fk_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tentative` FOREIGN KEY (`tentative_id`) REFERENCES `tentatives` (`id`) ON DELETE CASCADE;

COMMIT;