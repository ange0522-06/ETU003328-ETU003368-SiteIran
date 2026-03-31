# ETU003328-ETU003368-SiteIran

---

## Guide d'installation et de démarrage

### Prérequis
- Docker et Docker Compose installés

### 1. Cloner le projet
```bash
git clone <https://github.com/ange0522-06/ETU003328-ETU003368-SiteIran>
cd ETU003328-ETU003368-SiteIran
```

### 2. Lancer les conteneurs Docker
```bash
docker-compose up -d
```
Cela démarre :
- Le backoffice PHP/Apache (http://localhost:8081)
- La base de données MySQL
  Le frontoffice PHP/Apache (http://localhost:8082)

### 3. Initialiser la base de données
- Importez le fichier `db.sql` dans MySQL :
```bash
docker exec -it siteiran-mysql mysql -u root -p < /var/www/html/db.sql
```
- Ou connectez-vous au conteneur MySQL :
```bash
docker exec -it siteiran-mysql mysql -u root -p
mot de passe: root
```

### 4. Vérifier les permissions d'upload
```bash
docker exec -it siteiran-backoffice chmod -R 777 /var/www/html/uploads
```

### 5. Accéder à l'application
- **Backoffice** : [http://localhost:8081]
- **Frontoffice** :[http://localhost:8082]

### 6. Identifiants par défaut
- admin/admin123


