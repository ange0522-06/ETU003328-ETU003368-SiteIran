<?php
require_once __DIR__ . '/../app/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $auteur_id = $_POST['auteur_id'] ?? null;
    $categorie_id = $_POST['categorie_id'] ?? null;
    // Statut par défaut : Brouillon (id=2)
    $statut_id = 2;
    // Insertion initiale sans image_id
    $stmt = $pdo->prepare('INSERT INTO article (titre, contenu, auteur_id, categorie_id, statut_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$titre, $contenu, $auteur_id, $categorie_id, $statut_id]);
    $article_id = $pdo->lastInsertId();

    // Extraire la première image du contenu
    $image_id = null;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $contenu, $m)) {
        $img_src = $m[1];
        // Chercher l'id correspondant dans la table image (champ photo)
        $stmtImg = $pdo->prepare('SELECT id FROM image WHERE photo = ? LIMIT 1');
        $stmtImg->execute([$img_src]);
        $row = $stmtImg->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            // Si pas trouvé, chercher par nom de fichier (fin du chemin)
            $filename = basename($img_src);
            $stmtImg = $pdo->prepare('SELECT id FROM image WHERE photo LIKE ? ORDER BY id DESC LIMIT 1');
            $stmtImg->execute(['%' . $filename]);
            $row = $stmtImg->fetch(PDO::FETCH_ASSOC);
        }
        if ($row) {
            $image_id = $row['id'];
            // Mettre à jour l'article avec l'image_id
            $pdo->prepare('UPDATE article SET image_id = ? WHERE id = ?')->execute([$image_id, $article_id]);
        }
    }
    echo '<p>Article ajouté avec succès !</p>';
}

$auteurs = $pdo->query('SELECT id, nom FROM auteur')->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query('SELECT id, nom FROM categorie')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article | Backoffice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <script src="tinymce/js/tinymce/tinymce.min.js"></script>
    <style>
        /* Modal de redimensionnement */
        #resize-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #resize-modal.open { display: flex; }
        #resize-modal .modal-box {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            max-width: 600px;
            width: 95%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }
        #resize-modal h3 { margin: 0 0 16px; font-size: 1.1rem; }
        #preview-canvas {
            display: block;
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .resize-controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .resize-controls label { font-size: 0.9rem; }
        .resize-controls input[type=number] {
            width: 80px;
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .resize-controls input[type=range] { flex: 1; min-width: 120px; }
        .alt-field { margin-bottom: 16px; }
        .alt-field label { display: block; font-size: 0.9rem; margin-bottom: 4px; }
        .alt-field input {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .modal-actions button {
            padding: 8px 18px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        #btn-confirm-upload { background: #0066cc; color: #fff; }
        #btn-cancel-upload  { background: #eee; color: #333; }
        #upload-info { font-size: 0.8rem; color: #666; margin-top: 6px; }
    </style>
    <script>
    tinymce.init({
        selector: '#titre',
        plugins: 'lists link',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link',
        height: 200,
        license_key: 'gpl',
        branding: false,
        promotion: false,
        statusbar: false,
        automatic_uploads: false
    });

    tinymce.init({
        selector: '#contenu',
        plugins: 'lists link image preview',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link customimage preview',
        height: 200,
        license_key: 'gpl',
        branding: false,
        promotion: false,
        statusbar: false,
        automatic_uploads: false,
        setup: function (editor) {
            editor.ui.registry.addButton('customimage', {
                text: 'Insérer une image',
                icon: 'image',
                onAction: function () {
                    var input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = function (event) {
                        var file = event.target.files[0];
                        if (!file) return;
                        openResizeModal(file, editor);
                    };
                    input.click();
                }
            });
        }
    });

    // ──────────────────────────────────────────────
    // Modal de redimensionnement
    // ──────────────────────────────────────────────
    var _originalImage = null; // HTMLImageElement
    var _originalFile  = null;
    var _currentEditor = null;

    function openResizeModal(file, editor) {
        _originalFile  = file;
        _currentEditor = editor;

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                _originalImage = img;
                // Initialise les champs
                document.getElementById('inp-width').value  = img.naturalWidth;
                document.getElementById('inp-height').value = img.naturalHeight;
                document.getElementById('inp-quality').value = 85;
                document.getElementById('inp-alt').value = '';
                drawPreview(img.naturalWidth, img.naturalHeight);
                updateInfo();
                document.getElementById('resize-modal').classList.add('open');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    function drawPreview(w, h) {
        var canvas = document.getElementById('preview-canvas');
        // Affiche en 100% mais limite visuellement via CSS max-width
        canvas.width  = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(_originalImage, 0, 0, w, h);
    }

    function updateInfo() {
        var canvas = document.getElementById('preview-canvas');
        // Taille estimée en ko (JPEG q=quality)
        var q = parseInt(document.getElementById('inp-quality').value) / 100;
        canvas.toBlob(function(blob) {
            var ko = blob ? Math.round(blob.size / 1024) : '?';
            document.getElementById('upload-info').textContent =
                canvas.width + ' × ' + canvas.height + ' px — ~' + ko + ' Ko';
        }, 'image/jpeg', q);
    }

    // Garder le ratio
    var _lockRatio = true;
    document.addEventListener('DOMContentLoaded', function() {
        var inpW = document.getElementById('inp-width');
        var inpH = document.getElementById('inp-height');
        var inpQ = document.getElementById('inp-quality');
        var inpQNum = document.getElementById('inp-quality-num');

        inpW.addEventListener('input', function() {
            if (_lockRatio && _originalImage) {
                var ratio = _originalImage.naturalHeight / _originalImage.naturalWidth;
                inpH.value = Math.round(this.value * ratio);
            }
            drawPreview(parseInt(inpW.value)||1, parseInt(inpH.value)||1);
            updateInfo();
        });
        inpH.addEventListener('input', function() {
            if (_lockRatio && _originalImage) {
                var ratio = _originalImage.naturalWidth / _originalImage.naturalHeight;
                inpW.value = Math.round(this.value * ratio);
            }
            drawPreview(parseInt(inpW.value)||1, parseInt(inpH.value)||1);
            updateInfo();
        });
        inpQ.addEventListener('input', function() {
            inpQNum.value = this.value;
            updateInfo();
        });
        inpQNum.addEventListener('input', function() {
            inpQ.value = this.value;
            updateInfo();
        });

        document.getElementById('btn-cancel-upload').addEventListener('click', function() {
            document.getElementById('resize-modal').classList.remove('open');
        });

        document.getElementById('btn-confirm-upload').addEventListener('click', function() {
            var alt = document.getElementById('inp-alt').value.trim();
            if (!alt) { alert('Le texte alternatif est obligatoire.'); return; }

            var canvas  = document.getElementById('preview-canvas');
            var quality = parseInt(document.getElementById('inp-quality').value) / 100;
            var ext     = (_originalFile.name.split('.').pop().toLowerCase());
            var mimeType = (ext === 'png') ? 'image/png' : 'image/jpeg';

            canvas.toBlob(function(blob) {
                if (!blob) { alert('Erreur lors de la création du blob.'); return; }
                var formData = new FormData();
                formData.append('file', blob, _originalFile.name);
                formData.append('alt', alt);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload_image.php');
                xhr.onload = function() {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json && json.location) {
                            _currentEditor.insertContent(
                                '<img src="' + json.location + '" alt="' + alt.replace(/"/g, '&quot;') + '" />'
                            );
                            document.getElementById('resize-modal').classList.remove('open');
                        } else {
                            alert(json.error || 'Erreur upload');
                        }
                    } catch(e) {
                        alert('Réponse invalide du serveur : ' + xhr.responseText);
                    }
                };
                xhr.onerror = function() { alert('Erreur réseau lors de l\'upload.'); };
                xhr.send(formData);
            }, mimeType, quality);
        });
    });
    </script>
</head>
<body>
    <!-- Modal de redimensionnement -->
    <div id="resize-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-box">
            <h3 id="modal-title">Redimensionner l'image avant l'upload</h3>
            <canvas id="preview-canvas"></canvas>
            <div id="upload-info"></div>
            <div class="resize-controls">
                <label>Largeur : <input type="number" id="inp-width" min="1" max="5000"> px</label>
                <label>Hauteur : <input type="number" id="inp-height" min="1" max="5000"> px</label>
                <label title="Ratio conservé automatiquement">🔒 Ratio</label>
            </div>
            <div class="resize-controls">
                <label>Qualité :</label>
                <input type="range" id="inp-quality" min="10" max="100" value="85">
                <input type="number" id="inp-quality-num" min="10" max="100" value="85" style="width:55px">%
            </div>
            <div class="alt-field">
                <label for="inp-alt">Texte alternatif <em>(obligatoire — SEO &amp; accessibilité)</em> :</label>
                <input type="text" id="inp-alt" placeholder="Décrivez l'image en quelques mots">
            </div>
            <div class="modal-actions">
                <button id="btn-cancel-upload">Annuler</button>
                <button id="btn-confirm-upload">Uploader &amp; insérer</button>
            </div>
        </div>
    </div>

    <!-- Modal d'aperçu -->
    <div id="preview-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.15);z-index:10000;align-items:center;justify-content:center;">
      <div style="background:#fff;max-width:900px;width:90vw;min-height:300px;max-height:90vh;overflow:auto;padding:32px 24px;border-radius:8px;box-shadow:0 8px 32px #0002;position:relative;">
        <h2 style="margin-top:0">Aperçu</h2>
        <div id="preview-content" style="min-height:200px;"></div>
        <button onclick="closePreview()" style="position:absolute;right:16px;top:16px;">Fermer</button>
      </div>
    </div>

    <header><h1>Ajouter un article</h1></header>
    <main>
        <form method="post">
            <div>
                <label for="titre"><strong>Titre :</strong></label><br>
                <textarea id="titre" name="titre" aria-label="Titre de l'article" style="min-height:50px;height:60px;font-size:1.2em;"></textarea>
            </div>
            <div>
                <label for="contenu"><strong>Contenu :</strong></label><br>
                <textarea id="contenu" name="contenu" aria-label="Contenu de l'article"></textarea>
            </div>
            <div>
                <label for="auteur"><strong>Auteur :</strong></label>
                <select id="auteur" name="auteur_id">
                    <option value="">-- Aucun --</option>
                    <?php foreach ($auteurs as $auteur): ?>
                        <option value="<?= $auteur['id'] ?>"><?= htmlspecialchars($auteur['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="categorie"><strong>Catégorie :</strong></label>
                <select id="categorie" name="categorie_id">
                    <option value="">-- Aucune --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" onclick="showHTML()">Voir le HTML</button>
            <button type="submit">Enregistrer</button>
        </form>
        <section>
            <h2>Code HTML généré</h2>
            <pre id="htmlOutput" style="background:#f0f0f0;padding:10px;white-space:pre-wrap;"></pre>
        </section>
    </main>
    <nav>
        <ul>
            <li><a href="index.php">Login</a></li>
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="ajout-article.php">Ajouter un article</a></li>
        </ul>
    </nav>
    <script>
    function showHTML() {
        var ed = tinymce.get('contenu');
        document.getElementById('htmlOutput').textContent = ed ? ed.getContent() : '';
    }
    function showHTML() {
        var ed = tinymce.get('contenu');
        document.getElementById('htmlOutput').textContent = ed ? ed.getContent() : '';
    }
    // Fonction d'échappement simple pour le titre/catégorie
    function escapeHtml(text) {
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    function closePreview() {
        document.getElementById('preview-modal').style.display = 'none';
    }
    </script>
</body>
</html>