<?php
require_once __DIR__ . '/../app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $auteur_id = $_POST['auteur_id'] ?? null;
    $categorie_id = $_POST['categorie_id'] ?? null;
    $statut_id = 2; // Brouillon par défaut

    $stmt = $pdo->prepare('INSERT INTO article (titre, contenu, auteur_id, categorie_id, statut_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$titre, $contenu, $auteur_id, $categorie_id, $statut_id]);
    $article_id = $pdo->lastInsertId();

    // Extraction de la première image pour image_id
    $image_id = null;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $contenu, $m)) {
        $img_src = $m[1];
        $stmtImg = $pdo->prepare('SELECT id FROM image WHERE photo = ? LIMIT 1');
        $stmtImg->execute([$img_src]);
        $row = $stmtImg->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $filename = basename($img_src);
            $stmtImg = $pdo->prepare('SELECT id FROM image WHERE photo LIKE ? ORDER BY id DESC LIMIT 1');
            $stmtImg->execute(['%' . $filename]);
            $row = $stmtImg->fetch(PDO::FETCH_ASSOC);
        }
        if ($row) {
            $image_id = $row['id'];
            $pdo->prepare('UPDATE article SET image_id = ? WHERE id = ?')->execute([$image_id, $article_id]);
        }
    }
    echo '<p style="color:green;">Article ajouté avec succès !</p>';
}

$auteurs = $pdo->query('SELECT id, nom FROM auteur')->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query('SELECT id, nom FROM categorie')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article | Backoffice</title>
    <meta name="description" content="Ajouter un nouvel article dans le backoffice IranInfo. Saisie du titre, contenu, auteur, catégorie et image.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../assets/style.css">
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
    </style>
    <script>
    tinymce.init({
        selector: '#contenu',
        plugins: 'lists link image preview',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link customimage preview',
        height: 400,
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
            // Ajout : customisation du preview pour inclure catégorie et auteur
            editor.on('ExecCommand', function(e) {
                if (e.command === 'mcePreview') {
                    setTimeout(function() {
                        var previewWin = document.querySelector('.tox-dialog__body-content iframe');
                        if (previewWin && previewWin.contentDocument) {
                            var doc = previewWin.contentDocument;
                            // Récupérer valeurs catégorie et auteur
                            var cat = '';
                            var auteur = '';
                            var catSel = document.getElementById('categorie_id');
                            if (catSel) cat = catSel.options[catSel.selectedIndex].text;
                            var auteurSel = document.getElementById('auteur_id');
                            if (auteurSel) auteur = auteurSel.options[auteurSel.selectedIndex].text;
                            // Créer bloc info
                            var infoDiv = doc.createElement('div');
                            infoDiv.style = 'margin:24px 0 12px 0;padding:12px 18px;background:#f8f8f8;border-radius:6px;border:1px solid #eee;font-size:1.1em;color:#333;';
                            infoDiv.innerHTML =
                                '<b>Catégorie :</b> ' + (cat || '-') + '<br>' +
                                '<b>Auteur :</b> ' + (auteur || '-');
                            // Insérer en haut du body du preview
                            doc.body.insertBefore(infoDiv, doc.body.firstChild);
                        }
                    }, 300);
                }
            });
        }
    });

    // Modal redimensionnement (code inchangé)
    var _originalImage = null;
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
        canvas.width  = w;
        canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(_originalImage, 0, 0, w, h);
    }

    function updateInfo() {
        var canvas = document.getElementById('preview-canvas');
        var q = parseInt(document.getElementById('inp-quality').value) / 100;
        canvas.toBlob(function(blob) {
            var ko = blob ? Math.round(blob.size / 1024) : '?';
            document.getElementById('upload-info').textContent =
                canvas.width + ' × ' + canvas.height + ' px — ~' + ko + ' Ko';
        }, 'image/jpeg', q);
    }

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
<a href="#main-content" class="skip-link" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;background:#fff;color:#222;z-index:1000;">Aller au contenu principal</a>

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

    <div class="bo-layout">
        <!-- SIDEBAR -->
        <aside class="bo-sidebar">
            <div class="bo-sidebar-logo">
                <div class="bo-sidebar-logo-name">Guerre <span>Iran</span></div>
                <div class="bo-sidebar-logo-sub">Backoffice</div>
            </div>
            <div class="bo-nav-section">Contenu</div>
            <a href="accueil.php" class="bo-nav-item">Accueil</a>
            <a href="ajout-article.php" class="bo-nav-item active">Ajouter un article</a>
            <a href="liste-articles.php" class="bo-nav-item">Liste des articles</a>
            <div class="bo-nav-section">Système</div>
            <a href="index.php" class="bo-nav-item">Login</a>
        </aside>

        <!-- MAIN -->
        <main id="main-content" class="bo-main" tabindex="-1" aria-label="Contenu principal">
            <div class="bo-topbar">
                <div class="bo-page-title">Ajouter un article</div>
                
                <!-- Boutons en haut à droite (comme demandé) -->
                <div class="bo-topbar-actions" style="display:flex; gap:10px; align-items:center;">
                    <a href="ajout-article.php" class="btn btn-primary">+ Nouveau article</a>
                    <button type="submit" form="article-form" class="btn btn-success" style="min-width:160px;">
                        Enregistrer
                    </button>
                </div>
            </div>

            <div class="bo-content">
                <form id="article-form" method="post">
                    <!-- TinyMCE Toolbar déplacé en haut -->
                    <div class="bo-card" style="margin-bottom:20px;">
                        <div class="bo-card-header">
                            Outils de mise en forme (Titre & Contenu)
                        </div>
                        <div class="bo-card-body" style="padding:12px 18px;">
                            <textarea id="contenu" name="contenu" style="visibility:hidden; height:0;"></textarea>
                            <!-- TinyMCE va remplacer cette zone et afficher sa barre d'outils ici -->
                        </div>
                    </div>

                    <div class="bo-form-grid">
                        <!-- Colonne gauche -->
                        <div>
                           
                            <div class="bo-card" style="margin-top:16px">
                                <div class="bo-card-header">Code HTML généré</div>
                                <div class="bo-card-body">
                                    <button type="button" onclick="showHTML()" class="btn btn-ghost" style="margin-bottom:8px">Afficher</button>
                                    <pre id="htmlOutput" style="background:#f0f0f0;padding:10px;white-space:pre-wrap;"></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne droite : options -->
                        <div>
                            <div class="bo-card">
                                <div class="bo-card-header">Statut & Date</div>
                                <div class="bo-card-body">
                                    <p disabled><option selected>Brouillon</p>
                                    <div style="margin-top:10px">
                                        <label class="bo-label">Date de publication</label>
                                        <input type="text" class="bo-input" value="<?php echo date('d/m/Y H:i'); ?>" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="bo-card" style="margin-top:14px">
                                <div class="bo-card-header">Catégorie</div>
                                <div class="bo-card-body">
                                    <label class="bo-label" for="categorie_id">Catégorie *</label>
                                    <select id="categorie_id" name="categorie_id" class="bo-input" required>
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="bo-card" style="margin-top:14px">
                                <div class="bo-card-header">Auteur</div>
                                <div class="bo-card-body">
                                    <label class="bo-label" for="auteur_id">Auteur</label>
                                    <select id="auteur_id" name="auteur_id" class="bo-input">
                                        <option value="">-- Aucun --</option>
                                        <?php foreach ($auteurs as $auteur): ?>
                                            <option value="<?= $auteur['id'] ?>"><?= htmlspecialchars($auteur['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    function showHTML() {
        var ed = tinymce.get('contenu');
        var html = ed ? ed.getContent() : '';
        // Ajout catégorie, auteur, statut & date
        var catSel = document.getElementById('categorie_id');
        var auteurSel = document.getElementById('auteur_id');
        var cat = catSel ? catSel.options[catSel.selectedIndex].text : '';
        var auteur = auteurSel ? auteurSel.options[auteurSel.selectedIndex].text : '';
        var statutSel = document.querySelector('select[name="statut_id"]');
        var statut = statutSel ? statutSel.options[statutSel.selectedIndex].text : '';
        var datePub = document.querySelector('input[type="text"][class*="bo-input"][disabled]');
        var date = datePub ? datePub.value : '';
        var info = '<div style="margin-bottom:12px;padding:8px 12px;background:#f8f8f8;border-radius:6px;border:1px solid #eee;font-size:1em;color:#333;">' +
            '<b>Statut :</b> ' + (statut || '-') + '<br>' +
            '<b>Date de publication :</b> ' + (date || '-') + '<br>' +
            '<b>Catégorie :</b> ' + (cat || '-') + '<br>' +
            '<b>Auteur :</b> ' + (auteur || '-') +
            '</div>';
        document.getElementById('htmlOutput').textContent = info + html;
    }
    </script>
</body>
</html>