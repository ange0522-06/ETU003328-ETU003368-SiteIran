CREATE DATABASE siteiran;

USE siteiran;

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
insert into admin (username, password) VALUES
('admin', 'admin123'),
('editor', 'editor123'),
('test', 'test123');


CREATE TABLE categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

INSERT INTO categorie (nom) VALUES
('Actualités'),
('Politique'),
('International'),
('Analyse');

CREATE TABLE image(
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo VARCHAR(500) NOT NULL
);

CREATE TABLE auteur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(500) NOT NULL
);
insert INTO auteur (nom) VALUES
('Jean Dupont'),
('Marie Curie'),
('Ali Rezaei'),
('Sophie Martin');

CREATE TABLE statut (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

insert into statut (nom) VALUES
('Publié'),
('Brouillon'),
('Archivé');

CREATE TABLE article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    image_id INT,
    categorie_id INT,
    auteur_id INT,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut_id INT,
    
    FOREIGN KEY (image_id) REFERENCES image(id)
    ON DELETE SET NULL,
    FOREIGN KEY (categorie_id) REFERENCES categorie(id)
    ON DELETE SET NULL,
    FOREIGN KEY (auteur_id) REFERENCES auteur(id)
    ON DELETE SET NULL,
    FOREIGN KEY (statut_id) REFERENCES statut(id)
    ON DELETE SET NULL
);

