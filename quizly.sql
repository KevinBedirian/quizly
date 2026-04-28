-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mar. 28 avr. 2026 à 15:38
-- Version du serveur : 5.7.24
-- Version de PHP : 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `quizly`
--

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `intitule` varchar(255) NOT NULL,
  `proposition_a` varchar(255) NOT NULL,
  `proposition_b` varchar(255) NOT NULL,
  `proposition_c` varchar(255) NOT NULL,
  `proposition_d` varchar(255) NOT NULL,
  `reponse` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `intitule`, `proposition_a`, `proposition_b`, `proposition_c`, `proposition_d`, `reponse`) VALUES
(1, 'Qu\'est-ce que le test de Turing ?', 'Un test matériel', 'Un test d\'intelligence artificielle', 'Un test réseau', 'Un test logiciel', 'B'),
(2, 'Qui est le père de l\'informatique ?', 'Bill Gates', 'Alan Turing', 'Steve Jobs', 'Tim Berners-Lee', 'B'),
(3, 'Que signifie HTTP ?', 'HyperText Transfer Protocol', 'HighText Transfer Protocol', 'Hyper Transfer Text Process', 'HyperTool Protocol', 'A'),
(4, 'Que signifie HTTPS ?', 'HyperText Transfer Protocol Secure', 'Hyper Transfer Protocol Safe', 'High Text Protocol Secure', 'Hyper Secure Transfer Protocol', 'A'),
(5, 'Combien d\'octets contient une adresse IPv4 ?', '2', '4', '8', '16', 'B'),
(6, 'Qu\'est-ce que le DNS ?', 'Un serveur web', 'Un système de noms de domaine', 'Un protocole de mail', 'Un antivirus', 'B'),
(7, 'Quel langage s\'exécute dans le navigateur ?', 'Python', 'C++', 'JavaScript', 'Java', 'C'),
(8, 'Qu\'est-ce qu\'un algorithme ?', 'Un logiciel', 'Une suite d\'instructions', 'Un matériel', 'Un virus', 'B'),
(9, 'Qui a créé Linux ?', 'Bill Gates', 'Linus Torvalds', 'Steve Jobs', 'Dennis Ritchie', 'B'),
(10, 'Qu\'est-ce qu\'une base de données ?', 'Un jeu', 'Un stockage structuré', 'Un virus', 'Un OS', 'B'),
(11, 'Que signifie CPU ?', 'Central Process Unit', 'Central Processing Unit', 'Computer Personal Unit', 'Core Process Utility', 'B'),
(12, 'Qu\'est-ce qu\'une IP ?', 'Identifiant réseau', 'Programme', 'Langage', 'Serveur', 'A'),
(13, 'Combien de bits dans un octet ?', '4', '8', '16', '32', 'B'),
(14, 'Quel OS est open source ?', 'Windows', 'Linux', 'macOS', 'iOS', 'B'),
(15, 'Qu\'est-ce que le HTML ?', 'Langage de programmation', 'Langage de structure', 'OS', 'Protocole', 'B'),
(16, 'Qu\'est-ce que le CSS ?', 'Langage de style', 'Langage serveur', 'Base de données', 'OS', 'A'),
(17, 'Quel protocole pour envoyer des mails ?', 'HTTP', 'FTP', 'SMTP', 'TCP', 'C'),
(18, 'Qu\'est-ce qu\'un serveur ?', 'Ordinateur qui fournit un service', 'Client', 'Routeur', 'Switch', 'A'),
(19, 'Qu\'est-ce que le cloud ?', 'Stockage distant', 'Un câble', 'Un logiciel', 'Un virus', 'A'),
(20, 'Que signifie RAM ?', 'Random Access Memory', 'Read Access Memory', 'Run Access Memory', 'Real Application Memory', 'A'),
(21, 'Qu\'est-ce qu\'un bug ?', 'Erreur', 'Fonction', 'Programme', 'Serveur', 'A'),
(22, 'Qu\'est-ce qu\'un firewall ?', 'Pare-feu', 'Virus', 'OS', 'Langage', 'A'),
(23, 'Qu\'est-ce qu\'un VPN ?', 'Réseau privé virtuel', 'Serveur web', 'Base de données', 'Langage', 'A'),
(24, 'Qu\'est-ce qu\'un cookie web ?', 'Fichier stocké côté client', 'Virus', 'OS', 'Protocole', 'A'),
(25, 'Qu\'est-ce que SQL ?', 'Langage de requête', 'OS', 'Serveur', 'Virus', 'A'),
(26, 'Que signifie URL ?', 'Uniform Resource Locator', 'Universal Resource Link', 'Unique Resource Locator', 'Unified Resource Link', 'A'),
(27, 'Qu\'est-ce qu\'un navigateur ?', 'Logiciel web', 'Serveur', 'OS', 'Langage', 'A'),
(28, 'Exemple de navigateur ?', 'Chrome', 'Linux', 'MySQL', 'Apache', 'A'),
(29, 'Qu\'est-ce que FTP ?', 'Transfert de fichiers', 'Mail', 'Web', 'Sécurité', 'A'),
(30, 'Qu\'est-ce que TCP ?', 'Protocole réseau', 'OS', 'Langage', 'Serveur', 'A'),
(31, 'Qu\'est-ce que UDP ?', 'Protocole rapide', 'OS', 'Langage', 'Serveur', 'A'),
(32, 'Qu\'est-ce qu\'un IDE ?', 'Environnement de développement', 'Serveur', 'OS', 'Virus', 'A'),
(33, 'Langage compilé ?', 'C', 'HTML', 'CSS', 'SQL', 'A'),
(34, 'Langage interprété ?', 'JavaScript', 'C', 'C++', 'Rust', 'A'),
(35, 'Qu\'est-ce que Git ?', 'Outil de versioning', 'OS', 'Serveur', 'Langage', 'A'),
(36, 'Qui a créé Git ?', 'Linus Torvalds', 'Bill Gates', 'Jobs', 'Zuckerberg', 'A'),
(37, 'Qu\'est-ce qu\'un commit ?', 'Sauvegarde version', 'Bug', 'OS', 'Serveur', 'A'),
(38, 'Qu\'est-ce qu\'un repo ?', 'Dépôt de code', 'Serveur', 'OS', 'Langage', 'A'),
(39, 'Qu\'est-ce qu\'une API ?', 'Interface de programmation', 'OS', 'Langage', 'Serveur', 'A'),
(40, 'JSON sert à ?', 'Échange de données', 'Compiler', 'Stocker OS', 'Créer CPU', 'A'),
(41, 'XML sert à ?', 'Structurer données', 'Compiler', 'OS', 'Serveur', 'A'),
(42, 'Qu\'est-ce qu\'un framework ?', 'Structure logicielle', 'OS', 'Serveur', 'Langage', 'A'),
(43, 'Exemple framework JS ?', 'React', 'Linux', 'MySQL', 'Apache', 'A'),
(44, 'Qu\'est-ce que Node.js ?', 'Runtime JS', 'OS', 'Serveur physique', 'Langage', 'A'),
(45, 'Qu\'est-ce que Docker ?', 'Conteneurisation', 'OS', 'Langage', 'CPU', 'A'),
(46, 'Qu\'est-ce qu\'une VM ?', 'Machine virtuelle', 'Serveur', 'OS', 'Langage', 'A'),
(47, 'Qu\'est-ce qu\'un hyperviseur ?', 'Gestion VM', 'OS', 'Langage', 'CPU', 'A'),
(48, 'Qu\'est-ce qu\'un kernel ?', 'Noyau OS', 'Serveur', 'Langage', 'API', 'A'),
(49, 'Qu\'est-ce qu\'un thread ?', 'Processus léger', 'CPU', 'RAM', 'Disque', 'A'),
(50, 'Qu\'est-ce qu\'un process ?', 'Programme en cours', 'CPU', 'RAM', 'Disque', 'A'),
(51, 'Qu\'est-ce que la latence ?', 'Temps réponse', 'Stockage', 'CPU', 'RAM', 'A'),
(52, 'Qu\'est-ce que la bande passante ?', 'Capacité réseau', 'CPU', 'RAM', 'OS', 'A'),
(53, 'Qu\'est-ce qu\'un switch ?', 'Équipement réseau', 'Serveur', 'OS', 'Langage', 'A'),
(54, 'Qu\'est-ce qu\'un routeur ?', 'Routage réseau', 'CPU', 'RAM', 'OS', 'A'),
(55, 'Qu\'est-ce qu\'un LAN ?', 'Réseau local', 'Internet', 'Serveur', 'OS', 'A'),
(56, 'Qu\'est-ce qu\'un WAN ?', 'Réseau étendu', 'Local', 'CPU', 'RAM', 'A'),
(57, 'Qu\'est-ce qu\'un ping ?', 'Test réseau', 'CPU', 'RAM', 'OS', 'A'),
(58, 'Qu\'est-ce que SSH ?', 'Connexion sécurisée', 'OS', 'Langage', 'Serveur', 'A'),
(59, 'Qu\'est-ce que Telnet ?', 'Connexion non sécurisée', 'OS', 'Langage', 'Serveur', 'A'),
(60, 'Qu\'est-ce qu\'un malware ?', 'Logiciel malveillant', 'OS', 'Serveur', 'CPU', 'A'),
(61, 'Qu\'est-ce qu\'un ransomware ?', 'Virus avec rançon', 'OS', 'Serveur', 'CPU', 'A'),
(62, 'Qu\'est-ce qu\'un phishing ?', 'Arnaque', 'OS', 'Serveur', 'CPU', 'A'),
(63, 'Qu\'est-ce que le hashing ?', 'Transformation irréversible', 'CPU', 'RAM', 'OS', 'A'),
(64, 'Exemple hash ?', 'SHA-256', 'HTTP', 'FTP', 'TCP', 'A'),
(65, 'Qu\'est-ce que le chiffrement ?', 'Protection données', 'CPU', 'RAM', 'OS', 'A'),
(66, 'Qu\'est-ce qu\'un certificat SSL ?', 'Sécurité web', 'CPU', 'RAM', 'OS', 'A'),
(67, 'Qu\'est-ce que l\'IA ?', 'Intelligence artificielle', 'CPU', 'RAM', 'OS', 'A'),
(68, 'Qu\'est-ce que le machine learning ?', 'Apprentissage automatique', 'CPU', 'RAM', 'OS', 'A'),
(69, 'Qu\'est-ce qu\'un dataset ?', 'Données', 'CPU', 'RAM', 'OS', 'A'),
(70, 'Qu\'est-ce qu\'un modèle ?', 'Algo entraîné', 'CPU', 'RAM', 'OS', 'A'),
(71, 'Qu\'est-ce que Python ?', 'Langage', 'OS', 'Serveur', 'CPU', 'A'),
(72, 'Qu\'est-ce que Java ?', 'Langage', 'OS', 'Serveur', 'CPU', 'A'),
(73, 'Qu\'est-ce que C++ ?', 'Langage', 'OS', 'Serveur', 'CPU', 'A'),
(74, 'Qu\'est-ce que Rust ?', 'Langage', 'OS', 'Serveur', 'CPU', 'A'),
(75, 'Qu\'est-ce que Go ?', 'Langage', 'OS', 'Serveur', 'CPU', 'A'),
(76, 'Qu\'est-ce que PHP ?', 'Langage serveur', 'OS', 'CPU', 'RAM', 'A'),
(77, 'Qu\'est-ce que MySQL ?', 'Base de données', 'OS', 'CPU', 'RAM', 'A'),
(78, 'Qu\'est-ce que PostgreSQL ?', 'Base de données', 'OS', 'CPU', 'RAM', 'A'),
(79, 'Qu\'est-ce qu\'Apache ?', 'Serveur web', 'OS', 'CPU', 'RAM', 'A'),
(80, 'Qu\'est-ce que Nginx ?', 'Serveur web', 'OS', 'CPU', 'RAM', 'A'),
(81, 'Qu\'est-ce que GitHub ?', 'Plateforme code', 'OS', 'CPU', 'RAM', 'A'),
(82, 'Qu\'est-ce que GitLab ?', 'Plateforme code', 'OS', 'CPU', 'RAM', 'A'),
(83, 'Qu\'est-ce que Bitbucket ?', 'Plateforme code', 'OS', 'CPU', 'RAM', 'A'),
(84, 'Qu\'est-ce qu\'un fork ?', 'Copie projet', 'OS', 'CPU', 'RAM', 'A'),
(85, 'Qu\'est-ce qu\'une pull request ?', 'Demande fusion', 'OS', 'CPU', 'RAM', 'A'),
(86, 'Qu\'est-ce qu\'une stack ?', 'Ensemble techno', 'CPU', 'RAM', 'OS', 'A'),
(87, 'Qu\'est-ce qu\'une variable ?', 'Stockage valeur', 'CPU', 'RAM', 'OS', 'A'),
(88, 'Qu\'est-ce qu\'une fonction ?', 'Bloc code', 'CPU', 'RAM', 'OS', 'A'),
(89, 'Qu\'est-ce qu\'une boucle ?', 'Répétition code', 'CPU', 'RAM', 'OS', 'A'),
(90, 'Qu\'est-ce qu\'une condition ?', 'Test logique', 'CPU', 'RAM', 'OS', 'A'),
(91, 'Qu\'est-ce qu\'un tableau ?', 'Liste valeurs', 'CPU', 'RAM', 'OS', 'A'),
(92, 'Qu\'est-ce qu\'un objet ?', 'Structure complexe', 'CPU', 'RAM', 'OS', 'A'),
(93, 'Qu\'est-ce que JSON ?', 'Format données', 'CPU', 'RAM', 'OS', 'A'),
(94, 'Qu\'est-ce que YAML ?', 'Format config', 'CPU', 'RAM', 'OS', 'A'),
(95, 'Qu\'est-ce que Bash ?', 'Shell', 'CPU', 'RAM', 'OS', 'A'),
(96, 'Qu\'est-ce que PowerShell ?', 'Shell Windows', 'CPU', 'RAM', 'OS', 'A'),
(97, 'Qu\'est-ce que Kubernetes ?', 'Orchestration conteneurs', 'CPU', 'RAM', 'OS', 'A'),
(98, 'Qu\'est-ce que Terraform ?', 'Infrastructure as code', 'CPU', 'RAM', 'OS', 'A'),
(99, 'Qu\'est-ce que CI/CD ?', 'Déploiement automatisé', 'CPU', 'RAM', 'OS', 'A'),
(100, 'Qu\'est-ce que DevOps ?', 'Culture dev+ops', 'CPU', 'RAM', 'OS', 'A');

-- --------------------------------------------------------

--
-- Structure de la table `reponses`
--

CREATE TABLE `reponses` (
  `id` int(11) NOT NULL,
  `id_tentative` int(11) NOT NULL,
  `id_question` int(11) NOT NULL,
  `reponse_user` varchar(255) NOT NULL,
  `correction` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `tentatives`
--

CREATE TABLE `tentatives` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `moyenne_generale` decimal(4,2) NOT NULL,
  `role` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reponses`
--
ALTER TABLE `reponses`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tentatives`
--
ALTER TABLE `tentatives`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT pour la table `reponses`
--
ALTER TABLE `reponses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tentatives`
--
ALTER TABLE `tentatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
