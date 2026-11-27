<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document->name }} - MetaERP</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        slate: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- TinyMCE (Matching React DialogEditHtml) -->
    <script src="https://cdn.tiny.cloud/1/601a2kfecmwhj92bpfvqwu493mx5uw6p31qswtm55jzhiyz4/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
            }
            .document-paper {
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-100 text-slate-800 antialiased"
      x-data="{
          isEditing: false,
          editorInitialized: false,
          docHtml: @js($html),
          saving: false,
          saveSuccess: false,
          errorMessage: '',
          toggleEdit() {
              this.isEditing = !this.isEditing;
              if (this.isEditing && !this.editorInitialized) {
                  this.$nextTick(() => {
                      if (window.tinymce) {
                          tinymce.init({
                              selector: '#docTinymceEditor',
                              height: 650,
                              menubar: false,
                              plugins: [
                                  'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                                  'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                                  'insertdatetime', 'media', 'table', 'help', 'wordcount'
                              ],
                              toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                              setup: (editor) => {
                                  editor.on('change', () => {
                                      this.docHtml = editor.getContent();
                                  });
                              }
                          });
                          this.editorInitialized = true;
                      }
                  });
              }
          },
          async saveDocument() {
              this.saving = true;
              this.errorMessage = '';
              if (window.tinymce && tinymce.get('docTinymceEditor')) {
                  this.docHtml = tinymce.get('docTinymceEditor').getContent();
              }
              try {
                  const res = await fetch('{{ route('contracts.documents.update', $document->id) }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ html: this.docHtml })
                  });
                  const data = await res.json();
                  if (data.success) {
                      this.saveSuccess = true;
                      this.isEditing = false;
                      if (this.$refs.docContainer) {
                          this.$refs.docContainer.innerHTML = this.docHtml;
                      }
                      setTimeout(() => this.saveSuccess = false, 3000);
                  } else {
                      this.errorMessage = data.message || 'Failed to save document';
                  }
              } catch (err) {
                  this.errorMessage = err.message || 'Error saving document';
              } finally {
                  this.saving = false;
              }
          }
      }">

    <!-- Top Navigation Bar (Exact match to React MUI AppBar in Screenshot 4) -->
    <header class="bg-[#1e293b] text-white sticky top-0 z-50 shadow-md no-print border-b border-slate-700/60">
        <div class="px-4 sm:px-6 h-16 flex items-center justify-between">
            <!-- Left Side: Close Icon + Title -->
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ $backUrl ?? (url()->previous() ?: route('contracts.index')) }}"
                   class="text-white/80 hover:text-white p-1.5 rounded-full hover:bg-white/10 transition cursor-pointer flex items-center justify-center shrink-0"
                   title="Close and return to Contract">
                    <span class="material-icons text-2xl">close</span>
                </a>
                <h1 class="text-xl sm:text-2xl font-normal text-white tracking-wide truncate">
                    {{ $document->name }}
                </h1>
            </div>

            <!-- Right Side: Edit / Save / Download Actions -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Save Status Feedback -->
                <span x-show="saveSuccess" x-cloak class="text-xs text-emerald-400 font-medium px-2 py-1 bg-emerald-950/40 rounded border border-emerald-500/30">
                    Saved successfully!
                </span>

                <!-- In Edit Mode: Save Button -->
                <button type="button"
                        x-show="isEditing"
                        @click="saveDocument()"
                        :disabled="saving"
                        class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded shadow transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 uppercase tracking-wider"
                        x-cloak>
                    <span class="material-icons text-base" x-show="!saving">save</span>
                    <span x-text="saving ? 'Saving...' : 'Save'">Save</span>
                </button>

                <!-- Toggle Edit / View Button -->
                <button type="button"
                        @click="toggleEdit()"
                        class="px-4 py-1.5 text-white/90 hover:text-white font-medium text-sm rounded hover:bg-white/10 transition cursor-pointer uppercase tracking-wider">
                    <span x-text="isEditing ? 'Cancel' : 'Edit'">Edit</span>
                </button>

                <!-- Download HTML -->
                <a href="{{ route('contracts.documents.download', $document->id) }}"
                   class="p-2 text-white/80 hover:text-white rounded hover:bg-white/10 transition cursor-pointer flex items-center"
                   title="Download Document">
                    <span class="material-icons text-xl">download</span>
                </a>

                <!-- Print Document -->
                <button type="button"
                        onclick="window.print()"
                        class="p-2 text-white/80 hover:text-white rounded hover:bg-white/10 transition cursor-pointer flex items-center"
                        title="Print Document">
                    <span class="material-icons text-xl">print</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Error Toast -->
    <div x-show="errorMessage" x-cloak class="max-w-xl mx-auto mt-4 px-4 py-2.5 bg-red-100 border border-red-300 text-red-800 text-sm rounded-lg flex items-center justify-between shadow-sm">
        <span x-text="errorMessage"></span>
        <button type="button" @click="errorMessage = ''" class="text-red-600 hover:text-red-900 ml-4 font-bold">✕</button>
    </div>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto bg-slate-100">
        <!-- View Mode: Document Paper (Exact match to React DialogViewHtml & Images 3 & 4) -->
        <div x-show="!isEditing" class="p-4 sm:p-10 flex justify-start">
            <div class="document-paper p-10 border-dashed border border-gray-400 bg-white w-fit shadow-xs text-slate-900"
                 x-ref="docContainer">
                {!! $html !!}
            </div>
        </div>

        <!-- Edit Mode: Full-page Visual & HTML Editor (Matching React DialogEditHtml) -->
        <div x-show="isEditing" x-cloak class="p-4 sm:p-8 flex flex-col flex-1 w-full max-w-7xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-6 flex flex-col flex-1 gap-4 min-h-[700px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 text-xs text-slate-500 font-medium">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-base text-indigo-500">edit_note</span>
                        <span class="font-semibold text-slate-700">Document Editor</span>
                    </div>
                    <span>Make changes below and click <strong>SAVE</strong> in the top bar</span>
                </div>
                <!-- Visual TinyMCE Editor Container -->
                <div class="flex-1 min-h-[650px]">
                    <textarea id="docTinymceEditor" x-model="docHtml" class="w-full h-full min-h-[650px] font-mono text-xs p-4 bg-slate-50 border border-slate-300 rounded-lg">{!! $html !!}</textarea>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
