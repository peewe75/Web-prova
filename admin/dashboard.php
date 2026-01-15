<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Studio Legale BCS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>

<body class="bg-gray-900 text-white min-h-screen font-sans">

    <!-- Navbar -->
    <nav class="bg-gray-800 border-b border-gray-700 p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-400">admin_panel_settings</span>
                <span class="font-bold text-xl">Admin BCS</span>
            </div>
            <a href="logout.php" class="text-gray-400 hover:text-white text-sm">Esci</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-4 lg:p-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Sidebar: List of Posts -->
        <div
            class="lg:col-span-1 bg-gray-800 rounded-xl border border-gray-700 p-4 h-[calc(100vh-140px)] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">Articoli</h2>
                <div class="flex gap-2">
                    <button onclick="newItem()"
                        class="bg-green-500 hover:bg-green-600 text-gray-900 p-2 rounded-lg flex items-center justify-center"
                        title="Nuovo Articolo">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
            <div id="postsList" class="space-y-2">
                <!-- Posts injected here via JS -->
                <p class="text-gray-500 text-center py-4">Caricamento...</p>
            </div>
        </div>

        <!-- Main Area: Editor -->
        <div class="lg:col-span-2 bg-gray-800 rounded-xl border border-gray-700 p-6">
            <form id="postForm" class="flex flex-col h-full gap-4">
                <input type="hidden" name="id" id="postId">
                <input type="hidden" name="existing_image" id="existingImage">

                <div class="flex justify-between items-start gap-4">
                    <div class="flex-grow">
                        <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Titolo</label>
                        <input type="text" name="title" id="postTitle" required
                            class="w-full bg-gray-700 border border-gray-600 rounded p-3 text-lg font-bold focus:border-green-400 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Categoria</label>
                        <select name="category" id="postCategory"
                            class="w-full bg-gray-700 border border-gray-600 rounded p-2 focus:border-green-400 focus:outline-none">
                            <option value="Penale">Penale</option>
                            <option value="Civile">Civile</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Tech">Tech</option>
                            <option value="Sovraindebitamento">Sovraindebitamento</option>
                            <option value="Crisi">Crisi d'Impresa</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Data</label>
                        <input type="date" name="date" id="postDate"
                            class="w-full bg-gray-700 border border-gray-600 rounded p-2 focus:border-green-400 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Immagine Copertina</label>
                    <div class="flex items-center gap-4 mt-1">
                        <input type="file" name="image" id="postImageDisplay" class="text-sm text-gray-400"
                            accept="image/*">
                        <div id="currentImagePreview" class="h-10 w-10 rounded bg-gray-700 bg-cover bg-center hidden">
                        </div>
                        <span class="text-xs text-gray-400">Formato consigliato: 1200x675px (16:9)</span>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Sommario (Breve
                        descrizione)</label>
                    <textarea name="summary" id="postSummary" rows="2"
                        class="w-full bg-gray-700 border border-gray-600 rounded p-2 focus:border-green-400 focus:outline-none font-sans text-sm"></textarea>
                </div>

                <div>
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider">Pagina AI
                        (Opzionale)</label>
                    <div class="flex items-center gap-4 mt-1">
                        <input type="file" id="zipInput" accept=".zip,.html" class="hidden" onchange="uploadZip()">
                        <button type="button" onclick="document.getElementById('zipInput').click()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center gap-2 text-sm font-bold">
                            <span class="material-symbols-outlined text-[18px]">upload_file</span> Carica Pagina AI (ZIP
                            o HTML)
                        </button>
                        <span id="zipStatus" class="text-xs text-gray-400 italic">Nessun file caricato (Verrà usato
                            l'editor di testo)</span>
                        <input type="hidden" name="custom_url" id="customUrl">
                    </div>
                </div>

                <div class="flex-grow flex flex-col">
                    <label class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Contenuto
                        Articolo</label>
                    <div id="editor-container" class="bg-gray-100 text-black rounded h-64 flex-grow"></div>
                    <input type="hidden" name="content" id="hiddenContent">
                </div>

                <div class="pt-4 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" id="deleteBtn" onclick="deletePost()"
                        class="hidden px-4 py-2 rounded bg-red-500/10 text-red-500 border border-red-500/50 hover:bg-red-500 hover:text-white transition-colors">Elimina</button>
                    <button type="button" onclick="resetForm()"
                        class="px-4 py-2 rounded text-gray-400 hover:text-white">Annulla</button>
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-600 text-gray-900 font-bold px-6 py-2 rounded shadow-lg flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span> Salva Articolo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Init Quill
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Scrivi qui il tuo articolo...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link', 'clean']
                ]
            }
        });

        // Set Default Date
        document.getElementById('postDate').valueAsDate = new Date();

        // Load Posts
        fetchPosts();

        async function fetchPosts() {
            const res = await fetch('../api/blog.php');
            const posts = await res.json();
            renderList(posts);
        }

        function renderList(posts) {
            const list = document.getElementById('postsList');
            list.innerHTML = '';

            if (posts.length === 0) {
                list.innerHTML = '<p class="text-sm text-center text-gray-500 italic">Nessun articolo presente.</p>';
                return;
            }

            posts.forEach(post => {
                const el = document.createElement('div');
                el.className = 'bg-gray-700/50 p-3 rounded-lg border border-gray-700 hover:border-green-500 cursor-pointer transition-all';
                el.onclick = () => loadPost(post);
                el.innerHTML = `
                    <h3 class="font-bold text-sm text-white truncate">${post.title}</h3>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-xs text-green-400 bg-green-400/10 px-2 py-0.5 rounded">${post.category}</span>
                        <span class="text-xs text-gray-500">${post.date}</span>
                    </div>
                `;
                list.appendChild(el);
            });
        }

        function loadPost(post) {
            document.getElementById('postId').value = post.id;
            document.getElementById('postTitle').value = post.title;
            document.getElementById('postCategory').value = post.category;
            document.getElementById('postDate').value = post.date;
            document.getElementById('postSummary').value = post.summary;
            document.getElementById('existingImage').value = post.image;

            // Handle Image Preview
            const preview = document.getElementById('currentImagePreview');
            if (post.image) {
                preview.style.backgroundImage = `url('../${post.image}')`;
                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
            }

            // Handle Custom URL
            document.getElementById('customUrl').value = post.custom_url || '';
            const zipStatus = document.getElementById('zipStatus');
            if (post.custom_url) {
                zipStatus.innerHTML = `<span class='text-green-400'>Pagina collegata: <a href='../${post.custom_url}' target='_blank' class='underline'>Apri</a></span>`;
            } else {
                zipStatus.textContent = "Nessun file caricato (Verrà usato l'editor di testo)";
            }

            // Set Content
            quill.root.innerHTML = post.content;

            // Show Delete Button
            document.getElementById('deleteBtn').classList.remove('hidden');
        }

        function newItem() {
            resetForm();
        }

        function resetForm() {
            document.getElementById('postForm').reset();
            document.getElementById('postId').value = '';
            document.getElementById('existingImage').value = '';
            document.getElementById('currentImagePreview').classList.add('hidden');
            document.getElementById('customUrl').value = '';
            document.getElementById('zipStatus').textContent = "Nessun file caricato (Verrà usato l'editor di testo)";
            document.getElementById('postDate').valueAsDate = new Date();
            quill.root.innerHTML = '';
            document.getElementById('deleteBtn').classList.add('hidden');
        }

        // Save Logic
        document.getElementById('postForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            // Sync Quill content to hidden input
            document.getElementById('hiddenContent').value = quill.root.innerHTML;

            const formData = new FormData(e.target);
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            try {
                btn.disabled = true;
                btn.innerHTML = 'Salvataggio...';

                const res = await fetch('../api/blog.php', {
                    method: 'POST',
                    body: formData
                });

                const responseText = await res.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Server response:', responseText);
                    throw new Error('Risposta server non valida');
                }

                if (res.ok) {
                    alert('Articolo salvato con successo!');
                    try {
                        resetForm();
                    } catch (e) {
                        console.warn("Error resetting form:", e);
                    }
                    try {
                        fetchPosts(); // Refresh list
                    } catch (e) {
                        console.warn("Error refreshing list:", e);
                    }
                } else {
                    alert('Errore: ' + (data.message || 'Errore durante il salvataggio'));
                }

            } catch (err) {
                console.error('Save error:', err);
                alert('Errore: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        // Upload ZIP Logic
        async function uploadZip() {
            const input = document.getElementById('zipInput');
            if (input.files.length === 0) return;

            const file = input.files[0];
            const formData = new FormData();
            formData.append('zip_file', file);

            const status = document.getElementById('zipStatus');
            status.innerHTML = "<span class='text-yellow-400 animate-pulse'>Caricamento ed estrazione in corso...</span>";

            try {
                const res = await fetch('../api/upload_ai_page.php', {
                    method: 'POST',
                    body: formData
                });

                // Read response as text first
                const responseText = await res.text();

                let data;
                try {
                    // Try to parse as JSON
                    data = JSON.parse(responseText);
                } catch (e) {
                    // If response is not JSON, show raw text
                    status.innerHTML = `<span class='text-red-400'>Errore del server: ${responseText.substring(0, 200)}</span>`;
                    alert('Errore del server. Controlla la console per dettagli.');
                    console.error('Server response:', responseText);
                    return;
                }

                if (data.success) {
                    document.getElementById('customUrl').value = data.url;
                    status.innerHTML = `<span class='text-green-400'>Caricamento completato! Pagina collegata. <a href='../${data.url}' target='_blank' class='underline ml-2'>Anteprima</a></span>`;
                    alert('Pagina AI caricata con successo!');
                } else {
                    status.innerHTML = `<span class='text-red-400'>Errore: ${data.message}</span>`;
                    alert('Errore: ' + data.message + (data.error_code ? ' (Codice: ' + data.error_code + ')' : ''));
                }
            } catch (err) {
                console.error(err);
                status.innerHTML = `<span class='text-red-400'>Errore di connessione: ${err.message}</span>`;
                alert('Errore durante il caricamento del file: ' + err.message);
            }
            // Clear input so change event fires again if same file selected
            input.value = '';
        }

        // Delete Logic
        async function deletePost() {
            const id = document.getElementById('postId').value;
            if (!id) return;

            if (!confirm('Sei sicuro di voler eliminare questo articolo? Questa azione non può essere annullata.')) {
                return;
            }

            try {
                const res = await fetch(`../api/blog.php?id=${id}`, {
                    method: 'DELETE'
                });

                if (res.ok) {
                    alert('Articolo eliminato con successo.');
                    resetForm();
                    fetchPosts();
                } else {
                    alert('Errore durante l\'eliminazione.');
                }
            } catch (err) {
                console.error(err);
                alert('Errore di connessione');
            }
        }
        // AI Image generation removed - manual upload only
    </script>
</body>

</html>