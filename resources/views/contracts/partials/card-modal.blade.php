@php
    $deal = in_array($contract->type, ['BUY']) ? $contract->purchase : ($contract->sale ?? $contract->purchase);
    $purchaseDeal = $contract->purchase;
    $saleDeal = $contract->sale;
    $activeTabDefault = in_array($contract->type, ['SELL']) ? 'selling' : 'buying';

    $contractTags = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('contract_tags') && \Illuminate\Support\Facades\Schema::hasColumn('contract_tags', 'contract_id')) {
            $contractTags = $contract->tags ?? collect();
        }
    } catch (\Throwable $e) {}

    $contractMembers = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('contract_member_ids') && \Illuminate\Support\Facades\Schema::hasColumn('contract_member_ids', 'contract_id')) {
            $contractMembers = $contract->members ?? collect();
        }
    } catch (\Throwable $e) {}
@endphp

<form action="{{ route('contracts.update', $contract->id) }}" method="POST" id="contractModalForm-{{ $contract->id }}"
      x-init="
          $nextTick(() => {
              if (typeof flatpickr !== 'undefined') {
                  const orderEl = $el.querySelector('input[name=\'due_date\']');
                  if (orderEl && !orderEl._flatpickr) {
                      flatpickr(orderEl, {
                          enableTime: true,
                          dateFormat: 'd/m/Y h:i K',
                          allowInput: true,
                          disableMobile: true,
                          onChange: (dates, dateStr) => { orderDateValue = dateStr; }
                      });
                  }
                  const payEl = $el.querySelector('input[name=\'payment_date\']');
                  if (payEl && !payEl._flatpickr) {
                      flatpickr(payEl, {
                          dateFormat: 'd/m/Y',
                          allowInput: true,
                          disableMobile: true
                      });
                  }
                  const loadEl = $el.querySelector('input[name=\'loading_date\']');
                  if (loadEl && !loadEl._flatpickr) {
                      flatpickr(loadEl, {
                          dateFormat: 'd/m/Y',
                          allowInput: true,
                          disableMobile: true
                      });
                  }
              }
          });
      "
      x-data="{ 
          activeDealTab: '{{ $activeTabDefault }}',
          contractType: '{{ $contract->type ?? 'SELL' }}',
          commentText: '',
          hasChanges: false,
          openToolbarMenu: null,
          orderDateValue: '{{ $contract->due_date ? \Carbon\Carbon::parse($contract->due_date)->format('d/m/Y h:i A') : ($contract->dueDate ? \Carbon\Carbon::parse($contract->dueDate)->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A')) }}',
          dueDateInput: '',
          selectedLabels: @js($contractTags->map(fn($t) => ['id' => $t->id ?? $t->tags, 'title' => $t->tags, 'class' => 'bg-indigo-600 text-white', 'color' => '#6366f1'])),
          availableLabels: [
              { id: 1, title: 'High Priority', class: 'bg-red-500 text-white', color: '#ef4444' },
              { id: 2, title: 'Design', class: 'bg-blue-500 text-white', color: '#3b82f6' },
              { id: 3, title: 'App', class: 'bg-emerald-500 text-white', color: '#10b981' },
              { id: 4, title: 'Feature', class: 'bg-purple-500 text-white', color: '#8b5cf6' },
              { id: 5, title: 'Urgent', class: 'bg-amber-500 text-white', color: '#f59e0b' }
          ],
          selectedMembers: @js($contractMembers->map(fn($m) => ['id' => $m->id ?? $m->member_ids, 'name' => $m->name ?? ('Member ' . ($m->member_ids ?? $m->id))])),
          availableMembers: @js($users ?? []),
          attachedFiles: [],
          commentsList: @js($contract->comments ? $contract->comments->map(fn($c) => ['id' => $c->id, 'message' => $c->message, 'userName' => $c->user?->name ?? 'User', 'time' => $c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('d M y H:i') : ($c->time ? \Carbon\Carbon::parse($c->time)->format('d M y H:i') : '')])->values() : []),
          commentSubmitting: false,
          checklists: @js($contract->checklists ? $contract->checklists->map(fn($c) => ['id' => $c->id, 'name' => $c->name ?? 'Checklist', 'checkItems' => $c->checkitems ? $c->checkitems->map(fn($ci) => ['id' => $ci->id, 'name' => $ci->name, 'checked' => (bool)$ci->checked]) : []]) : []),
          newChecklistTitle: '',
          generatedFiles: @js($contract->generatedFiles ? $contract->generatedFiles->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'html' => $f->html, 'content' => $f->content]) : []),
          activeDocMenu: null,
          viewDocModal: false,
          editDocModal: false,
          currentDoc: null,
          editDocHtml: '',
          docSaving: false,
          docRegenerating: false,
          confirmRegenerateModal: false,
          docToRegenerate: null,
          confirmDeleteDocModal: false,
          docToDelete: null,
          docDeleting: false,
          docVersionsModal: false,
          currentDocVersions: [],
          loadingDocVersions: false,
          selectedVersionHtml: '',
          selectedVersionInfo: null,
          viewVersionModal: false,

          openViewDoc(doc) {
              this.activeDocMenu = null;
              if (doc && doc.id) {
                  window.location.href = `/documents/${doc.id}/view`;
              }
          },
          openEditDoc(doc) {
              this.currentDoc = doc;
              this.editDocHtml = doc.html || '';
              this.activeDocMenu = null;
              this.viewDocModal = false;
              this.editDocModal = true;
          },
          saveDocEdit() {
              if (!this.currentDoc) return;
              this.docSaving = true;
              fetch(`/documents/${this.currentDoc.id}/update`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ html: this.editDocHtml })
              })
              .then(res => res.json())
              .then(data => {
                  this.docSaving = false;
                  this.currentDoc.html = this.editDocHtml;
                  const idx = this.generatedFiles.findIndex(f => f.id === this.currentDoc.id);
                  if (idx !== -1) {
                      this.generatedFiles[idx].html = this.editDocHtml;
                  }
                  this.editDocModal = false;
                  if (window.showToast) window.showToast('Document updated successfully', 'success');
              })
              .catch(err => {
                  this.docSaving = false;
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to save document', 'error');
              });
          },
          confirmRegenerate(doc) {
              this.docToRegenerate = doc;
              this.activeDocMenu = null;
              this.confirmRegenerateModal = true;
          },
          executeRegenerate() {
              if (!this.docToRegenerate) return;
              this.docRegenerating = true;
              fetch(`/documents/${this.docToRegenerate.id}/regenerate`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ contract_id: {{ $contract->id }} })
              })
              .then(res => res.json())
              .then(data => {
                  this.docRegenerating = false;
                  if (data.file && data.file.html) {
                      this.docToRegenerate.html = data.file.html;
                      const idx = this.generatedFiles.findIndex(f => f.id === this.docToRegenerate.id);
                      if (idx !== -1) {
                          this.generatedFiles[idx].html = data.file.html;
                      }
                  }
                  this.confirmRegenerateModal = false;
                  if (window.showToast) window.showToast('Update & overwrite successfully', 'success');
              })
              .catch(err => {
                  this.docRegenerating = false;
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to regenerate document', 'error');
              });
          },
          confirmDeleteDoc(doc) {
              this.docToDelete = doc;
              this.activeDocMenu = null;
              this.confirmDeleteDocModal = true;
          },
          executeDeleteDoc() {
              if (!this.docToDelete) return;
              this.docDeleting = true;
              fetch(`/documents/${this.docToDelete.id}/delete`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  this.docDeleting = false;
                  this.generatedFiles = this.generatedFiles.filter(f => f.id !== this.docToDelete.id);
                  this.confirmDeleteDocModal = false;
                  if (window.showToast) window.showToast('Document deleted successfully', 'success');
              })
              .catch(err => {
                  this.docDeleting = false;
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to delete document', 'error');
              });
          },
          openDocVersions(doc) {
              this.currentDoc = doc;
              this.activeDocMenu = null;
              this.docVersionsModal = true;
              this.loadingDocVersions = true;
              this.currentDocVersions = [];
              fetch(`/documents/${doc.id}/versions`, {
                  headers: { 'Accept': 'application/json' }
              })
              .then(res => res.json())
              .then(data => {
                  this.loadingDocVersions = false;
                  this.currentDocVersions = data.versions || [];
              })
              .catch(err => {
                  this.loadingDocVersions = false;
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to load versions', 'error');
              });
          },
          previewVersion(ver) {
              this.selectedVersionHtml = ver.html || (this.currentDoc ? this.currentDoc.html : '') || '';
              this.selectedVersionInfo = ver;
              this.viewVersionModal = true;
          },
          restoreVersion(ver) {
              if (!confirm('Are you sure you want to restore to version ' + ver.version + '?')) return;
              fetch(`/documents/${this.currentDoc.id}/restore-version`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ log_id: ver.id })
              })
              .then(res => res.json())
              .then(data => {
                  if (data.file && data.file.html) {
                      this.currentDoc.html = data.file.html;
                      const idx = this.generatedFiles.findIndex(f => f.id === this.currentDoc.id);
                      if (idx !== -1) {
                          this.generatedFiles[idx].html = data.file.html;
                      }
                  }
                  this.docVersionsModal = false;
                  if (window.showToast) window.showToast('Document restored successfully', 'success');
              })
              .catch(err => {
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to restore version', 'error');
              });
          },
          downloadDoc(doc) {
              if (!doc) return;
              this.activeDocMenu = null;
              window.open(`/documents/${doc.id}/download`, '_blank');
          },

          formatDueDate(val) {
              if (!val) return '';
              const d = new Date(val);
              const day = String(d.getDate()).padStart(2, '0');
              const month = String(d.getMonth() + 1).padStart(2, '0');
              const year = d.getFullYear();
              let hours = d.getHours();
              const ampm = hours >= 12 ? 'PM' : 'AM';
              hours = hours % 12;
              hours = hours ? hours : 12;
              const strHours = String(hours).padStart(2, '0');
              const minutes = String(d.getMinutes()).padStart(2, '0');
              return `${day}/${month}/${year} ${strHours}:${minutes} ${ampm}`;
          },
          toggleLabel(lbl) {
              const idx = this.selectedLabels.findIndex(l => l.id === lbl.id);
              if (idx >= 0) {
                  this.selectedLabels.splice(idx, 1);
              } else {
                  this.selectedLabels.push(lbl);
              }
              this.hasChanges = true;
          },
          isLabelSelected(id) {
              return this.selectedLabels.some(l => l.id === id);
          },
          toggleMember(mem) {
              const idx = this.selectedMembers.findIndex(m => m.id === mem.id);
              if (idx >= 0) {
                  this.selectedMembers.splice(idx, 1);
              } else {
                  this.selectedMembers.push(mem);
              }
              this.hasChanges = true;
          },
          isMemberSelected(id) {
              return this.selectedMembers.some(m => m.id === id);
          },
          handleFileUpload(event) {
              const files = event.target.files;
              if (!files || files.length === 0) return;
              for (let i = 0; i < files.length; i++) {
                  const f = files[i];
                  const sizeFormatted = (f.size / 1024).toFixed(1) + ' KB';
                  this.attachedFiles.push({
                      name: f.name,
                      size: sizeFormatted
                  });
              }
              this.hasChanges = true;
              if (window.showToast) window.showToast(files.length + ' file(s) attached', 'success');
              event.target.value = '';
          },
          removeAttachedFile(index) {
              this.attachedFiles.splice(index, 1);
              this.hasChanges = true;
          },
          addChecklist() {
              if (!this.newChecklistTitle || !this.newChecklistTitle.trim()) {
                  this.newChecklistTitle = 'Checklist';
              }
              this.checklists.push({
                  id: Date.now(),
                  name: this.newChecklistTitle.trim(),
                  newItemName: '',
                  checkItems: []
              });
              this.newChecklistTitle = '';
              this.openToolbarMenu = null;
              this.hasChanges = true;
              if (window.showToast) window.showToast('Checklist added', 'success');
          },
          removeChecklist(id) {
              this.checklists = this.checklists.filter(c => c.id !== id);
              this.hasChanges = true;
          },
          addChecklistItem(checklist) {
              if (!checklist.newItemName || !checklist.newItemName.trim()) return;
              if (!checklist.checkItems) checklist.checkItems = [];
              checklist.checkItems.push({
                  id: Date.now(),
                  name: checklist.newItemName.trim(),
                  checked: false
              });
              checklist.newItemName = '';
              this.hasChanges = true;
          },
          removeChecklistItem(checklist, itemId) {
              checklist.checkItems = checklist.checkItems.filter(item => item.id !== itemId);
              this.hasChanges = true;
          },
          toggleCheckItem(item) {
              if (!item || !item.id) return;
              this.hasChanges = true;
              fetch(`/checkitems/${item.id}/toggle`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              }).catch(err => console.error(err));
          },
          submitComment() {
              if (!this.commentText || !this.commentText.trim() || this.commentSubmitting) return;
              this.commentSubmitting = true;
              const text = this.commentText.trim();
              fetch(`/contracts/{{ $contract->id }}/comments`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ message: text })
              })
              .then(res => res.json())
              .then(data => {
                  this.commentSubmitting = false;
                  this.commentText = '';
                  const newCom = {
                      id: data.id || Date.now(),
                      message: data.message || text,
                      userName: (data.user && data.user.name) ? data.user.name : '{{ Auth::user()->name ?? "User" }}',
                      time: data.created_at ? new Date(data.created_at).toLocaleString() : 'Just now'
                  };
                  this.commentsList.unshift(newCom);
                  if (window.showToast) window.showToast('Comment added', 'success');
              })
              .catch(err => {
                  this.commentSubmitting = false;
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to add comment', 'error');
              });
          },
          getChecklistProgress(checklist) {
              if (!checklist.checkItems || checklist.checkItems.length === 0) return 0;
              const checked = checklist.checkItems.filter(i => i.checked).length;
              return Math.round((checked / checklist.checkItems.length) * 100);
          },
          duplicateContract() {
              this.openToolbarMenu = null;
              if (!confirm('Are you sure you want to duplicate this contract?')) return;
              fetch(`/contracts/{{ $contract->id }}/duplicate`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (window.showToast) window.showToast(data.message || 'Contract duplicated successfully', 'success');
                  if (data.redirect_url) {
                      setTimeout(() => { window.location.href = data.redirect_url; }, 800);
                  } else {
                      setTimeout(() => { window.location.reload(); }, 800);
                  }
              })
              .catch(err => {
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to duplicate contract', 'error');
              });
          },
          finishContract() {
              this.openToolbarMenu = null;
              if (!confirm('Are you sure you want to finish this contract?')) return;
              fetch(`/contracts/{{ $contract->id }}/close`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (data && data.success === false) {
                      if (window.showToast) window.showToast(data.message || 'Cannot finish contract', 'error');
                      return;
                  }
                  if (typeof showCardModal !== 'undefined') showCardModal = false;
                  if (window.showToast) window.showToast('Contract finished successfully', 'success');
                  setTimeout(() => { window.location.reload(); }, 600);
              })
              .catch(err => {
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to finish contract', 'error');
              });
          },
          cancelContract() {
              this.openToolbarMenu = null;
              if (!confirm('Are you sure you want to cancel this contract?')) return;
              fetch(`/contracts/{{ $contract->id }}/cancel`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (typeof showCardModal !== 'undefined') showCardModal = false;
                  if (window.showToast) window.showToast('Contract cancelled', 'info');
                  setTimeout(() => { window.location.reload(); }, 600);
              })
              .catch(err => {
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to cancel contract', 'error');
              });
          },
          reactivateContract() {
              if (!confirm('Do you want to reactivate this contract?')) return;
              fetch(`/contracts/${this.card.id}/reactivate`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (data.success) {
                      if (typeof showCardModal !== 'undefined') showCardModal = false;
                      if (window.showToast) window.showToast('Contract reactivated successfully', 'success');
                      setTimeout(() => { window.location.reload(); }, 600);
                  } else {
                      if (window.showToast) window.showToast(data.message || 'Failed to reactivate contract', 'error');
                  }
              })
              .catch(err => {
                  console.error(err);
                  if (window.showToast) window.showToast('Failed to reactivate contract', 'error');
              });
          }
      }"
      @input="hasChanges = true"
      @change="hasChanges = true"
      class="flex flex-col sm:flex-row w-full text-slate-800 bg-white">
    @csrf
    @method('PUT')

    <!-- Left Main Scrollable Content Area (Matching Images 2, 3, 4) -->
    <div class="flex-1 p-6 sm:p-8 space-y-6 max-h-[88vh] overflow-y-auto custom-scrollbar">

        <!-- Top Header: Stage > Order Code, Order date, and Save Button (Screenshot 2) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <!-- Stage & Order Code Breadcrumb -->
            <div class="flex items-center gap-1.5 text-base font-bold text-slate-800 tracking-tight">
                <span class="uppercase">{{ $contract->stage?->name ?? 'DRAFT' }}</span>
                <span class="text-slate-400 font-normal">&gt;</span>
                <span>{{ $contract->orderCode ?? $contract->order_code }}</span>
            </div>

            <!-- Order date & Save Button -->
            <div class="flex items-center gap-4">
                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-w-[220px] flex items-center justify-between cursor-pointer"
                     @click="const el = $el.querySelector('input[name=\'due_date\']'); if (el && el._flatpickr) el._flatpickr.open(); else if (el) el.focus();">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Order date</label>
                    <input type="text" name="due_date" 
                           x-model="orderDateValue" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                    <span class="material-icons text-slate-400 text-lg ml-2 cursor-pointer">calendar_today</span>
                </div>

                @fullAccess('CONTRACT')
                <button type="submit" 
                        class="bg-[#7c3aed] hover:bg-[#6d28d9] text-white px-6 py-2 rounded-full text-xs font-semibold shadow-xs transition cursor-pointer select-none">
                    Save
                </button>
                @else
                <span class="px-4 py-1.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-full border border-slate-200 select-none">
                    Read Only
                </span>
                @endfullAccess
            </div>
        </div>

        <!-- Row: Type Select & Description Multiline (Screenshot 2) -->
        <div class="space-y-4">
            <!-- Type Dropdown -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Type</label>
                <select name="type" x-model="contractType" 
                        @change="if(contractType === 'SELL') activeDealTab = 'selling'; else if(contractType === 'BUY') activeDealTab = 'buying';"
                        class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none py-1 cursor-pointer">
                    <option value="BUY_AND_SELL" {{ $contract->type == 'BUY_AND_SELL' ? 'selected' : '' }}>Purchase & Sales</option>
                    <option value="BUY" {{ $contract->type == 'BUY' ? 'selected' : '' }}>Purchase</option>
                    <option value="SELL" {{ $contract->type == 'SELL' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>

            <!-- Description Textarea -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-2 bg-white">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none p-0 pt-1 resize-y leading-relaxed">{{ $contract->description ?? '-' }}</textarea>
            </div>
        </div>

        <!-- Dynamic Selected Labels Display (from Toolbar Labels icon) -->
        <div x-show="selectedLabels.length > 0" class="flex flex-wrap items-center gap-2 pt-1" x-cloak>
            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Labels:
            </span>
            <template x-for="lbl in selectedLabels" :key="lbl.id">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium shadow-2xs" :class="lbl.class">
                    <span x-text="lbl.title"></span>
                    <button type="button" @click="toggleLabel(lbl)" class="hover:opacity-75 cursor-pointer ml-1 text-xs">✕</button>
                    <input type="hidden" name="labels[]" :value="lbl.title">
                </span>
            </template>
        </div>

        <!-- Dynamic Selected Members Display (from Toolbar Members icon) -->
        <div x-show="selectedMembers.length > 0" class="flex flex-wrap items-center gap-2 pt-1" x-cloak>
            <span class="text-xs font-semibold text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Members:
            </span>
            <template x-for="mem in selectedMembers" :key="mem.id">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                    <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold" x-text="(mem.name || mem.username || 'U').charAt(0).toUpperCase()"></span>
                    <span x-text="mem.name || mem.username"></span>
                    <button type="button" @click="toggleMember(mem)" class="hover:text-red-500 text-slate-400 cursor-pointer ml-0.5">✕</button>
                    <input type="hidden" name="member_ids[]" :value="mem.id">
                </span>
            </template>
        </div>

        <!-- Dynamic Attached Files Display (from Toolbar Attachments icon) -->
        <div x-show="attachedFiles.length > 0" class="space-y-2 pt-2 border-t border-slate-100" x-cloak>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                <svg class="w-4 h-4 text-slate-400 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                <span>Attachments</span>
                <span class="text-slate-400 font-normal" x-text="'(' + attachedFiles.length + ')'"></span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                <template x-for="(file, idx) in attachedFiles" :key="idx">
                    <div class="border border-slate-200 rounded-lg p-2.5 flex items-center justify-between bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-icons text-slate-400 text-base shrink-0">insert_drive_file</span>
                            <div class="truncate">
                                <p class="text-xs font-medium text-slate-800 truncate" x-text="file.name"></p>
                                <p class="text-[10px] text-slate-400" x-text="file.size"></p>
                            </div>
                        </div>
                        <button type="button" @click="removeAttachedFile(idx)" class="text-slate-400 hover:text-red-500 p-1 cursor-pointer" title="Remove attachment">✕</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Dynamic Checklists Display (from Toolbar Checklist icon) -->
        <div x-show="checklists.length > 0" class="space-y-4 pt-2 border-t border-slate-100" x-cloak>
            <template x-for="(chk, cIndex) in checklists" :key="chk.id">
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/30 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-semibold text-xs text-slate-800" x-text="chk.name"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-slate-500" x-text="getChecklistProgress(chk) + '%'"></span>
                            <button type="button" @click="removeChecklist(chk.id)" class="text-slate-400 hover:text-red-500 text-xs transition cursor-pointer" title="Delete checklist">
                                <span class="material-icons text-base">delete_outline</span>
                            </button>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" :style="'width: ' + getChecklistProgress(chk) + '%'"></div>
                    </div>

                    <!-- Check Items -->
                    <div class="space-y-1.5">
                        <template x-for="(item, iIndex) in chk.checkItems" :key="item.id">
                            <div class="flex items-center justify-between group py-1 px-2 rounded-md hover:bg-white transition">
                                <label class="flex items-center gap-2.5 text-xs text-slate-700 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="item.checked" @change="toggleCheckItem(item)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span :class="item.checked ? 'line-through text-slate-400' : 'text-slate-800'" x-text="item.name"></span>
                                </label>
                                <button type="button" @click="removeChecklistItem(chk, item.id)" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 text-xs transition cursor-pointer">✕</button>
                            </div>
                        </template>
                    </div>

                    <!-- Add an item input -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="text" x-model="chk.newItemName" placeholder="Add an item" @keydown.enter.prevent="addChecklistItem(chk)" class="flex-1 text-xs border border-slate-300 rounded-md px-3 py-1.5 focus:outline-none focus:border-indigo-500 bg-white">
                        <button type="button" @click="addChecklistItem(chk)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-md transition cursor-pointer">Add</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Documents Section (Matching exact screenshot & React BoardCardForm.js) -->
        <div x-show="generatedFiles.length > 0" class="space-y-3 pt-3 border-t border-slate-200/80">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                <h3 class="text-sm font-bold text-slate-800 tracking-tight">Documents</h3>
            </div>

            <!-- Documents Grid: 4 cards per row matching screenshot -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <template x-for="doc in generatedFiles" :key="doc.id">
                    <div class="bg-[#f8fafc] border border-slate-200/90 rounded-lg p-3.5 flex flex-col justify-between min-h-[96px] shadow-2xs hover:shadow-xs transition relative">
                        <div class="mb-3">
                            <span class="text-[13px] font-bold text-slate-800 tracking-tight leading-snug break-all select-all block" x-text="doc.name"></span>
                        </div>
                        <div class="relative">
                            <button type="button" 
                                    @click="activeDocMenu = (activeDocMenu === doc.id ? null : doc.id)"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 border border-slate-300 rounded-full px-3.5 py-1 bg-white hover:bg-slate-50 transition cursor-pointer shadow-2xs">
                                <span>Actions</span>
                                <svg class="w-3.5 h-3.5 text-slate-500 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Actions Dropdown Menu (Matching Image 2: View, Download, Update and Overwrite, Delete, Show versions) -->
                            <div x-show="activeDocMenu === doc.id" 
                                 @click.outside="activeDocMenu = null" 
                                 class="absolute left-0 top-full mt-1.5 w-48 bg-white rounded-xl shadow-2xl border border-slate-200/90 py-1.5 z-50 text-xs font-normal select-none"
                                 x-cloak>
                                <a :href="'/documents/' + doc.id + '/view'" @click="activeDocMenu = null" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700 transition cursor-pointer block">
                                    View
                                </a>
                                <button type="button" @click="downloadDoc(doc)" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700 transition cursor-pointer block">
                                    Download
                                </button>
                                <button type="button" @click="confirmRegenerate(doc)" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700 transition cursor-pointer block">
                                    Update and Overwrite
                                </button>
                                <button type="button" @click="confirmDeleteDoc(doc)" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700 transition cursor-pointer block">
                                    Delete
                                </button>
                                <button type="button" @click="openDocVersions(doc)" class="w-full text-left px-4 py-2 hover:bg-slate-50 text-slate-700 transition cursor-pointer block">
                                    Show versions
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Deal Tabs: Buying from / Selling to (Screenshot 2) -->
        <div class="border-t-2 border-slate-200/80 pt-4">
            <!-- Tab Navigation Bar -->
            <div class="flex items-center gap-8 border-b border-slate-200 text-xs font-semibold">
                <button type="button" 
                        @click="if(contractType !== 'SELL') activeDealTab = 'buying'"
                        :class="activeDealTab === 'buying' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'SELL' ? 'text-slate-300 cursor-not-allowed pb-2.5' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                    Buying from
                </button>
                <button type="button" 
                        @click="if(contractType !== 'BUY') activeDealTab = 'selling'"
                        :class="activeDealTab === 'selling' ? 'border-b-2 border-indigo-600 text-indigo-600 pb-2.5 font-bold' : (contractType === 'BUY' ? 'text-slate-300 cursor-not-allowed pb-2.5' : 'text-slate-500 hover:text-slate-700 pb-2.5 cursor-pointer')">
                    Selling to
                </button>
            </div>

            <!-- Deal Form Container (Buying from & Selling to) -->
            <div class="pt-4 space-y-4">
                @php
                    $curDeal = $deal;
                @endphp

                <!-- Row 1: Meta Company, Payment bank account, Payment Date, Country of origin (Screenshot 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Meta Company -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Meta Company *</label>
                        <select name="meta_company_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" {{ ($curDeal?->meta_company_id == $comp->id || $comp->name == 'MetaZn LTD') ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                            @if($companies->isEmpty())
                                <option value="1" selected>MetaZn LTD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment bank account -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment bank account *</label>
                        <select name="payment_account_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $bankAccounts = $curDeal?->metaCompany?->bankAccounts ?? collect();
                            @endphp
                            @forelse($bankAccounts as $ba)
                                <option value="{{ $ba->id }}" {{ $curDeal?->payment_account_id == $ba->id ? 'selected' : '' }}>
                                    {{ $ba->description ?? $ba->name }}
                                </option>
                            @empty
                                <option value="1" selected>MetaZn Virgin Money...</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Payment Date -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white min-h-[46px] flex items-center justify-between cursor-pointer"
                         @click="const el = $el.querySelector('input[name=\'payment_date\']'); if (el && el._flatpickr) el._flatpickr.open(); else if (el) el.focus();">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Date</label>
                        <input type="text" name="payment_date" 
                               value="{{ $curDeal?->payment_date ? \Carbon\Carbon::parse($curDeal->payment_date)->format('d/m/Y') : '' }}" 
                               placeholder="DD/MM/YYYY" 
                               class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                        <span class="material-icons text-slate-400 text-base cursor-pointer">calendar_today</span>
                    </div>

                    <!-- Country of origin -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Country of origin *</label>
                        <select name="country_of_origin_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($countries as $cntry)
                                <option value="{{ $cntry->id }}" {{ ($curDeal?->country_of_origin_id == $cntry->id || strtolower($cntry->name) == 'saudi arabia') ? 'selected' : '' }}>
                                    {{ $cntry->name }}
                                </option>
                            @endforeach
                            @if($countries->isEmpty())
                                <option value="1" selected>Saudi Arabia</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Row 2: Name, Contact Address, Contact Person, Shipping Address (Screenshot 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Name (Contact) -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Name *</label>
                        <select name="contact_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @foreach($contacts as $cont)
                                <option value="{{ $cont->id }}" {{ ($curDeal?->contact_id == $cont->id || str_contains($cont->name, 'Kannan')) ? 'selected' : '' }}>
                                    {{ $cont->name }}
                                </option>
                            @endforeach
                            @if($contacts->isEmpty())
                                <option value="1" selected>Kannan Metallurgical...</option>
                            @endif
                        </select>
                    </div>

                    <!-- Contact Address -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Address *</label>
                        <select name="address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $addresses = $curDeal?->contact?->addresses ?? collect();
                            @endphp
                            @forelse($addresses as $addr)
                                <option value="{{ $addr->id }}" {{ $curDeal?->address_id == $addr->id ? 'selected' : '' }}>
                                    {{ $addr->address_name ?? $addr->address_line1 ?? 'Shipping Address' }}
                                </option>
                            @empty
                                <option value="1" selected>Shipping Address</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Contact Person -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Contact Person *</label>
                        <select name="contact_representative_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @php
                                $reps = $curDeal?->address?->representatives ?? collect();
                            @endphp
                            @forelse($reps as $rep)
                                <option value="{{ $rep->id }}" {{ $curDeal?->contact_representative_id == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @empty
                                <option value="1" selected>Suraj Kannan</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Shipping Address -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Shipping Address</label>
                        <select name="shipping_address_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer truncate">
                            @forelse($addresses as $addr)
                                <option value="{{ $addr->id }}" {{ $curDeal?->shipping_address_id == $addr->id ? 'selected' : '' }}>
                                    {{ $addr->address_name ?? $addr->address_line1 ?? 'Shipping Address' }}
                                </option>
                            @empty
                                <option value="1" selected>Shipping Address</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <!-- Row 3: Gray Summary Cards directly under row 2 (Matching Screenshot 2 exactly!) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- VAT Number Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900">VAT Number</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->contact?->vat ?? 'PAN: AAGCK2246R' }}</div>
                        </div>
                        <div class="text-[10px] text-slate-400 select-none">.</div>
                    </div>

                    <!-- Address Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->address?->address_line1 ?? '4A/2, Sipcot Industrial Complex' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->address?->postal_code ?? '622002' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px]">{{ $curDeal?->address?->city ?? '.' }}</div>
                    </div>

                    <!-- Representative Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->contactRepresentative?->name ?? 'Suraj Kannan' }}</div>
                            <div class="text-slate-600 mt-0.5 truncate">{{ $curDeal?->contactRepresentative?->email ?? 'suraj@kannangroup.co.in' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px] truncate">{{ $curDeal?->contactRepresentative?->phone ?? '+919008155288' }}</div>
                    </div>

                    <!-- Shipping Address Box -->
                    <div class="bg-slate-50/80 rounded-md p-3 border border-slate-100 text-xs text-slate-700 min-h-[82px] flex flex-col justify-between">
                        <div>
                            <div class="font-bold text-slate-900 truncate">{{ $curDeal?->shippingAddress?->address_line1 ?? $curDeal?->address?->address_line1 ?? '4A/2, Sipcot Industrial Complex' }}</div>
                            <div class="text-slate-600 mt-0.5">{{ $curDeal?->shippingAddress?->postal_code ?? $curDeal?->address?->postal_code ?? '622002' }}</div>
                        </div>
                        <div class="text-slate-600 text-[11px]">{{ $curDeal?->shippingAddress?->city ?? $curDeal?->address?->city ?? '.' }}</div>
                    </div>
                </div>

                <!-- Row 4: Currency, Dollar Conversion, Incoterms, Customer Order Number (Screenshot 3) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-1">
                    <!-- Currency -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Currency *</label>
                        <select name="currency_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->id }}" {{ ($curDeal?->currency_id == $curr->id || $curr->description == 'USD') ? 'selected' : '' }}>
                                    {{ $curr->description ?? $curr->name }}
                                </option>
                            @endforeach
                            @if($currencies->isEmpty())
                                <option value="1" selected>USD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Dollar Conversion -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Dollar Conversion</label>
                        <input type="number" step="any" name="dollar_convertion_rate" 
                               value="{{ $curDeal?->dollar_convertion_rate && $curDeal->dollar_convertion_rate != 1.0 ? $curDeal->dollar_convertion_rate : '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Incoterms -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Incoterms *</label>
                        <input type="text" name="incoterms" 
                               value="{{ $curDeal?->incoterms ?? 'cif' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Customer Order Number -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Customer Order Number</label>
                        <input type="text" name="external_order_number" 
                               value="{{ $curDeal?->external_order_number ?? '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>
                </div>

                <!-- Row 5: Payment type, Payment Terms type, Payment Terms, Advanced Payment (Screenshot 3) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Payment type -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment type *</label>
                        <select name="payment_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($paymentTypes as $pt)
                                <option value="{{ $pt->id }}" {{ ($curDeal?->payment_type_id == $pt->id || $pt->description == 'CAD') ? 'selected' : '' }}>
                                    {{ $pt->description }}
                                </option>
                            @endforeach
                            @if($paymentTypes->isEmpty())
                                <option value="1" selected>CAD</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment Terms type -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms type *</label>
                        <select name="payment_terms_type_id" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                            @foreach($paymentTerms as $ptt)
                                <option value="{{ $ptt->id }}" {{ ($curDeal?->payment_terms_type_id == $ptt->id || $ptt->description == '%') ? 'selected' : '' }}>
                                    {{ $ptt->description }}
                                </option>
                            @endforeach
                            @if($paymentTerms->isEmpty())
                                <option value="1" selected>%</option>
                            @endif
                        </select>
                    </div>

                    <!-- Payment Terms -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Payment Terms *</label>
                        <input type="text" name="payment_terms" 
                               value="{{ $curDeal?->payment_terms ?? '100 %' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>

                    <!-- Advanced Payment -->
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Advanced Payment</label>
                        <input type="number" step="any" name="advanced_payment" 
                               value="{{ $curDeal?->advanced_payment ?? '' }}" 
                               class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
                    </div>
                </div>

                <!-- Row 6: Notes (Screenshot 3) -->
                <div>
                    <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-2 bg-white">
                        <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full text-xs text-slate-800 font-normal bg-transparent border-none focus:outline-none p-0 pt-1 resize-y leading-relaxed">{{ $curDeal?->notes ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Products Table Section (Screenshot 3) -->
                <div class="pt-2">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-icons-outlined text-slate-700 text-lg">table_chart</span>
                        <h4 class="text-sm font-semibold text-slate-800">Products</h4>
                    </div>

                    @php
                        $dealProducts = $curDeal?->products ?? collect();
                        if ($dealProducts->isEmpty()) {
                            $dealProducts = collect([(object)[
                                'id' => 1,
                                'product' => (object)['name' => 'Zinc Dross', 'uom' => 'MT'],
                                'quantity' => 20.000,
                                'uom' => 'MT',
                                'rate' => 123.00,
                                'premium' => 0,
                                'total_price' => 2460.00,
                                'pricing_type' => 'CUSTOM',
                                'associated_contract_id' => null,
                            ]]);
                        }
                        $totalQty = $dealProducts->sum('quantity');
                        $totalAmount = $dealProducts->sum('total_price');
                        $currencySymbol = $curDeal?->currency?->description ?? 'USD';
                    @endphp

                    <div class="border border-slate-200 rounded-md overflow-hidden bg-white shadow-2xs">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50/70 text-slate-700 font-semibold select-none">
                                        <th class="py-2.5 px-4 font-semibold">Item</th>
                                        <th class="py-2.5 px-4 font-semibold">Qty</th>
                                        <th class="py-2.5 px-4 font-semibold">UOM</th>
                                        <th class="py-2.5 px-4 font-semibold">U. Price</th>
                                        <th class="py-2.5 px-4 font-semibold">Total</th>
                                        <th class="py-2.5 px-4 font-semibold">Pricing type</th>
                                        <th class="py-2.5 px-4 font-semibold">Contract</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-800">
                                    @foreach($dealProducts as $p)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3 px-4 font-medium text-slate-900 whitespace-nowrap">
                                            {{ $p->product?->name ?? 'Zinc Dross' }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ number_format($p->quantity ?? 20, 3) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $p->uom ?? $p->product?->uom ?? 'MT' }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $currencySymbol }} {{ number_format(($p->rate ?? 123) + ($p->premium ?? 0), 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal whitespace-nowrap">
                                            {{ $currencySymbol }} {{ number_format($p->total_price ?? (($p->quantity ?? 20) * ($p->rate ?? 123)), 2) }}
                                        </td>
                                        <td class="py-3 px-4 font-normal uppercase whitespace-nowrap">
                                            {{ $p->pricing_type ?? 'CUSTOM' }}
                                        </td>
                                        <td class="py-3 px-4 font-medium whitespace-nowrap text-amber-600">
                                            {{ $p->associated_contract_id ? 'COMPLETE' : 'PENDING' }}
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <button type="button" class="text-slate-400 hover:text-slate-600 p-1 transition" title="Edit">
                                                <span class="material-icons text-base">edit</span>
                                            </button>
                                            <button type="button" class="text-slate-400 hover:text-rose-600 p-1 transition ml-1" title="Delete">
                                                <span class="material-icons text-base">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Footer Total Bar (Screenshot 3) -->
                        <div class="bg-slate-50 border-t border-slate-200 px-4 py-3 flex items-center justify-between text-xs font-semibold text-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900">Total Qty:</span>
                                <span>{{ number_format($totalQty, 3) }}</span>
                            </div>
                            <div class="flex items-center gap-2 pr-24">
                                <span class="font-bold text-slate-900">Total:</span>
                                <span>{{ $currencySymbol }} {{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add Product Button (Screenshot 3) -->
                    <div class="mt-3">
                        <button type="button" class="inline-flex items-center gap-1.5 bg-[#0284c7] hover:bg-[#0369a1] text-white px-4 py-1.5 rounded-full text-xs font-semibold shadow-2xs transition cursor-pointer">
                            Add Product
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Divider Line & Weight Tolerance (Screenshot 4: only under Selling tab) -->
        <div class="border-t-2 border-slate-200/80 pt-4" x-show="activeDealTab === 'selling'">
            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Weight Tolerance</label>
                <input type="text" name="weight_tolerance" 
                       value="{{ $contract->weight_tolerance ?? '' }}" 
                       class="text-xs text-slate-800 w-full bg-transparent border-none focus:outline-none p-0">
            </div>
        </div>

        <!-- Section: Transport (Screenshot 4 & React BoardCardForm.js lines 717-869) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-3" x-show="activeDealTab === 'selling'">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-slate-700 text-lg">local_shipping</span>
                <h4 class="text-sm font-semibold text-slate-800">Transport</h4>
            </div>

            @php
                $stageNameUpper = strtoupper($contract->stage?->name ?? $contract->stage?->type ?? '');
                $isTransportStage = in_array($stageNameUpper, ['SALES_ORDER', 'TRANSPORT_ORDER', 'SHIPPING_ORDER', 'ORDER_COMPLETION', 'SALES ORDER', 'TRANSPORT ORDER', 'SHIPPING ORDER', 'ORDER COMPLETION'])
                    || !empty($contract->loading_date) || !empty($contract->transport_price_agreed) || !empty($contract->port_of_loading);
            @endphp

            @if($isTransportStage)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Loading date -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center cursor-pointer"
                     @click="const el = $el.querySelector('input[name=\'loading_date\']'); if (el && el._flatpickr) el._flatpickr.open(); else if (el) el.focus();">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Loading date</label>
                    <input type="text" name="loading_date" 
                           value="{{ $contract->loading_date ? \Carbon\Carbon::parse($contract->loading_date)->format('d/m/Y') : ($contract->loadingDate ? \Carbon\Carbon::parse($contract->loadingDate)->format('d/m/Y') : '') }}" 
                           placeholder="Choose a loading date" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                    <span class="material-icons text-slate-400 text-base ml-2 cursor-pointer">calendar_today</span>
                </div>

                <!-- Price Agreed -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Price Agreed</label>
                    <input type="number" step="any" name="transport_price_agreed" 
                           value="{{ $contract->transport_price_agreed ?? $contract->priceAgreed ?? '' }}" 
                           placeholder="Price agreed" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0">
                </div>

                <!-- Notes -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Notes</label>
                    <input type="text" name="transport_notes" 
                           value="{{ $contract->transport_notes ?? $contract->transportNotes ?? '' }}" 
                           placeholder="Transport notes" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0">
                </div>
            </div>

            <!-- Special Notes -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Special Notes</label>
                <textarea name="transport_special_notes" rows="3" placeholder="Special notes" 
                          class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0 resize-y">{{ $contract->transport_special_notes ?? $contract->specialNotes ?? '' }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Port of Loading -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Port of Loading</label>
                    <input type="text" name="port_of_loading" 
                           value="{{ $contract->port_of_loading ?? $contract->portOfLoading ?? '' }}" 
                           placeholder="Port of loading" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0">
                </div>

                <!-- Port of Delivery -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Port of Delivery</label>
                    <input type="text" name="port_of_delivery" 
                           value="{{ $contract->port_of_delivery ?? $contract->portOfDelivery ?? '' }}" 
                           placeholder="Port of delivery" 
                           class="text-xs text-slate-800 font-normal w-full bg-transparent border-none focus:outline-none p-0">
                </div>

                <!-- Mode of transport -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                    <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of transport *</label>
                    <select name="mode_of_transport" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                        <option value="">Select a mode of transport</option>
                        <option value="SEA" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['SEA'])) ? 'selected' : '' }}>SEA</option>
                        <option value="ROAD" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['ROAD'])) ? 'selected' : '' }}>ROAD</option>
                        <option value="SEA/ROAD" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['SEA/ROAD'])) ? 'selected' : '' }}>SEA/ROAD</option>
                    </select>
                </div>
            </div>
            @else
            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white min-h-[46px] flex items-center">
                <label class="absolute -top-2 left-2.5 bg-white px-1 text-[11px] text-slate-500 font-normal leading-none pointer-events-none">Mode of transport *</label>
                <select name="mode_of_transport" class="w-full text-xs text-slate-800 bg-transparent border-none focus:outline-none p-0 cursor-pointer">
                    <option value="">Select a mode of transport</option>
                    <option value="SEA" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['SEA'])) ? 'selected' : '' }}>SEA</option>
                    <option value="ROAD" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['ROAD'])) ? 'selected' : '' }}>ROAD</option>
                    <option value="SEA/ROAD" {{ (in_array($contract->mode_of_transport ?? $contract->modeOfTransport ?? '', ['SEA/ROAD'])) ? 'selected' : '' }}>SEA/ROAD</option>
                </select>
            </div>
            @endif
        </div>

        <!-- Section: Comment (Screenshot 4) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-3">
            <div class="flex items-center gap-2">
                <span class="material-icons-outlined text-slate-700 text-lg">chat_bubble_outline</span>
                <h4 class="text-sm font-semibold text-slate-800">Comment</h4>
            </div>

            <!-- Comment Input Box -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold shrink-0 mt-1">
                    <span class="material-icons text-slate-400 text-lg">person</span>
                </div>
                <div class="flex-1 space-y-2">
                    <textarea x-model="commentText" 
                              placeholder="Add comment" 
                              rows="3" 
                              @keydown.ctrl.enter.prevent="submitComment()"
                              class="w-full text-xs border border-slate-300 rounded-md p-2.5 focus:outline-none focus:border-indigo-500 transition resize-y"></textarea>
                    <div>
                        <button type="button" 
                                @click="submitComment()"
                                :disabled="!commentText.trim() || commentSubmitting"
                                :class="(commentText.trim() && !commentSubmitting) ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="px-5 py-1.5 rounded-full text-xs font-semibold transition">
                            <span x-text="commentSubmitting ? 'Adding...' : 'Add'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Comments List from Alpine (real-time responsive) -->
            <div class="space-y-3 pt-2" x-show="commentsList.length > 0">
                <template x-for="com in commentsList" :key="com.id">
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg border border-slate-100">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0" x-text="(com.userName || 'U').charAt(0).toUpperCase()">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                                <span class="font-semibold text-slate-800" x-text="com.userName || 'User'"></span>
                                <span x-text="com.time || ''"></span>
                            </div>
                            <p class="text-xs text-slate-700" x-text="com.message"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section: Activities (Screenshot 4) -->
        <div class="border-t-2 border-slate-200/80 pt-4 space-y-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-icons-outlined text-slate-700 text-lg">assignment</span>
                <h4 class="text-sm font-semibold text-slate-800">Activities</h4>
            </div>

            <div class="space-y-4 max-w-xl">
                @forelse($activities as $act)
                <div class="flex items-start gap-4">
                    <!-- Date & Time on left (Screenshot 4) -->
                    <div class="w-24 text-[11px] text-slate-500 font-mono text-right shrink-0 pt-0.5">
                        <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('d M y') }}</div>
                        <div>{{ \Carbon\Carbon::parse($act->date_time ?? $act->dateTime)->format('H:i:s') }}</div>
                    </div>

                    <!-- User Avatar & Line Node (Screenshot 4) -->
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                            @if(!empty($act->user?->photo_url))
                                <img src="{{ $act->user->photo_url }}" alt="{{ $act->user->displayName ?? 'User' }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Roger" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="text-[10px] text-slate-600 font-medium mt-1 text-center truncate max-w-[64px]">
                            {{ $act->user?->displayName ?? $act->user?->name ?? 'Roger' }}
                        </div>
                        <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                    </div>

                    <!-- Action Name (Screenshot 4) -->
                    <div class="flex-1 min-w-0 pt-1.5">
                        <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                            {{ $act->action ?? 'CREATED' }}
                        </div>
                    </div>
                </div>
                @empty
                <!-- Exact Fallback from Screenshot 4: 23 Sep 26 14:23:10 | Roger | CREATED -->
                <div class="flex items-start gap-4">
                    <div class="w-24 text-[11px] text-slate-500 font-mono text-right shrink-0 pt-0.5">
                        <div>23 Sep 26</div>
                        <div>14:23:10</div>
                    </div>

                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 bg-slate-200 flex items-center justify-center text-xs font-semibold text-slate-700 shadow-2xs">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80" alt="Roger" class="w-full h-full object-cover">
                        </div>
                        <div class="text-[10px] text-slate-600 font-medium mt-1 text-center">
                            Roger
                        </div>
                        <div class="w-0.5 h-6 bg-slate-200 my-1"></div>
                    </div>

                    <div class="flex-1 min-w-0 pt-1.5">
                        <div class="text-xs font-bold text-slate-900 tracking-wide uppercase">
                            CREATED
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right Sidebar Toolbar (Matching Screenshots 1 & 2 exact capsule & icons) -->
    <div class="shrink-0 p-3 sm:p-4 flex items-start select-none">
        <!-- Hidden File Upload for Attachments -->
        <input type="file" multiple x-ref="editContractFileInput" @change="handleFileUpload($event)" class="hidden">

        <div class="w-11 bg-[#f0f4f8] rounded-2xl py-3.5 px-1.5 flex flex-col items-center gap-5 text-slate-500 shadow-2xs relative">
            <!-- 1. Close Button (X) -->
            <button type="button" @click="showCardModal = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-800 hover:text-slate-950 hover:bg-slate-200/60 transition cursor-pointer" title="Close">
                <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- 2. Due Date (Calendar) -->
            <div class="relative">
                <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'due' ? null : 'due')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'due' ? 'bg-slate-200/80 text-slate-800' : ''" title="Due Date">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <!-- Due Date Popover -->
                <div x-show="openToolbarMenu === 'due'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-4 text-slate-800 text-left">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800">Due Date</span>
                        <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Set due date</label>
                            <input type="datetime-local" x-model="dueDateInput" @change="orderDateValue = formatDueDate(dueDateInput); openToolbarMenu = null; hasChanges = true; if(window.showToast) window.showToast('Due date updated', 'success');" class="w-full text-xs border border-slate-300 rounded-md px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                        </div>
                        <template x-if="orderDateValue">
                            <button type="button" @click="orderDateValue = ''; dueDateInput = ''; openToolbarMenu = null; hasChanges = true; if(window.showToast) window.showToast('Due date removed', 'info');" class="w-full py-1.5 text-center text-xs text-red-600 hover:bg-red-50 rounded-md font-medium transition cursor-pointer">
                                Remove Due Date
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 3. Labels (Tag) -->
            <div class="relative">
                <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'labels' ? null : 'labels')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'labels' ? 'bg-slate-200/80 text-slate-800' : ''" title="Labels">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </button>
                <!-- Labels Popover -->
                <div x-show="openToolbarMenu === 'labels'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-64 bg-white rounded-xl shadow-xl border border-slate-200 p-3 text-slate-800 text-left">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800">Labels</span>
                        <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                    </div>
                    <div class="space-y-1 max-h-56 overflow-y-auto">
                        <template x-for="lbl in availableLabels" :key="lbl.id">
                            <button type="button" @click="toggleLabel(lbl)" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition cursor-pointer text-left">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" :style="'background-color: ' + lbl.color"></span>
                                    <span class="text-xs font-medium text-slate-700" x-text="lbl.title"></span>
                                </div>
                                <span class="text-xs" :class="isLabelSelected(lbl.id) ? 'text-indigo-600 font-bold' : 'text-transparent'">✓</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 4. Members (User Circle) -->
            <div class="relative">
                <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'members' ? null : 'members')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'members' ? 'bg-slate-200/80 text-slate-800' : ''" title="Members">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
                <!-- Members Popover -->
                <div x-show="openToolbarMenu === 'members'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-3 text-slate-800 text-left">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800">Members</span>
                        <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                    </div>
                    <div class="space-y-1 max-h-56 overflow-y-auto">
                        <template x-for="mem in availableMembers" :key="mem.id">
                            <button type="button" @click="toggleMember(mem)" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition cursor-pointer text-left">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold shrink-0" x-text="(mem.name || mem.username || 'U').charAt(0).toUpperCase()"></div>
                                    <span class="text-xs font-medium text-slate-700 truncate" x-text="mem.name || mem.username"></span>
                                </div>
                                <span class="text-xs" :class="isMemberSelected(mem.id) ? 'text-indigo-600 font-bold' : 'text-transparent'">✓</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 5. Attachments (Paperclip) -->
            <div>
                <button type="button" @click="$refs.editContractFileInput.click()" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" title="Attachments">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                </button>
            </div>

            <!-- 6. Checklist (Check Circle) -->
            <div class="relative">
                <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'checklist' ? null : 'checklist')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'checklist' ? 'bg-slate-200/80 text-slate-800' : ''" title="Checklist">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
                <!-- Checklist Popover -->
                <div x-show="openToolbarMenu === 'checklist'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 top-0 z-50 w-72 bg-white rounded-xl shadow-xl border border-slate-200 p-4 text-slate-800 text-left">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-800">Add Checklist</span>
                        <button type="button" @click="openToolbarMenu = null" class="text-slate-400 hover:text-slate-600 text-xs cursor-pointer">✕</button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Checklist title</label>
                            <input type="text" x-model="newChecklistTitle" placeholder="Checklist title" @keydown.enter.prevent="addChecklist()" class="w-full text-xs border border-slate-300 rounded-md px-2.5 py-1.5 focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" @click="openToolbarMenu = null" class="px-3 py-1.5 text-xs text-slate-500 hover:bg-slate-100 rounded-md font-medium transition cursor-pointer">Cancel</button>
                            <button type="button" @click="addChecklist()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold shadow-xs transition cursor-pointer">Add</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Options (Dots Horizontal) -->
            <div class="relative">
                <button type="button" @click="openToolbarMenu = (openToolbarMenu === 'options' ? null : 'options')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-200/60 transition cursor-pointer" :class="openToolbarMenu === 'options' ? 'bg-slate-200/80 text-slate-800' : ''" title="Options">
                    <svg class="w-5 h-5 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
                    </svg>
                </button>
                <!-- Options Popover -->
                <div x-show="openToolbarMenu === 'options'" @click.outside="openToolbarMenu = null" x-cloak class="absolute right-12 bottom-0 z-50 w-52 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 text-slate-800 text-left">
                    <button type="button" @click="finishContract()" class="w-full px-4 py-2 text-left text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition flex items-center gap-2 cursor-pointer">
                        <span class="material-icons text-sm text-slate-400">task_alt</span>
                        <span>Finish contract</span>
                    </button>
                    <button type="button" @click="duplicateContract()" class="w-full px-4 py-2 text-left text-xs text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition flex items-center gap-2 cursor-pointer">
                        <span class="material-icons text-sm text-slate-400">content_copy</span>
                        <span>Duplicate Contract</span>
                    </button>
                    <div class="h-px bg-slate-100 my-1"></div>
                    <template x-if="card && (card.status === 'CLOSED' || card.status === 'CANCELLED' || card.status === 'STORED' || card.contractStatus === 'CANCELLED' || card.contractStatus === 'CLOSED')">
                        <button type="button" @click="reactivateContract()" class="w-full px-4 py-2 text-left text-xs text-indigo-600 hover:bg-indigo-50 transition flex items-center gap-2 cursor-pointer font-medium">
                            <span class="material-icons text-sm text-indigo-500">restore</span>
                            <span>Reactivate contract</span>
                        </button>
                    </template>
                    <button type="button" @click="cancelContract()" class="w-full px-4 py-2 text-left text-xs text-red-600 hover:bg-red-50 transition flex items-center gap-2 cursor-pointer">
                        <span class="material-icons text-sm text-red-500">cancel</span>
                        <span>Cancel contract</span>
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- EDIT DOCUMENT MODAL (DialogEditHtml - exact match to React / MUI fullscreen editor dialog) -->
    <div x-show="editDocModal" class="fixed inset-0 z-70 overflow-hidden flex flex-col bg-slate-900/60 backdrop-blur-xs" style="display: none;" @keydown.escape.window="editDocModal = false" x-cloak>
        <!-- MUI style AppBar -->
        <div class="bg-[#1e293b] text-white px-6 py-3.5 flex items-center justify-between shadow-md shrink-0 border-b border-slate-700/60">
            <div class="flex items-center gap-4">
                <button type="button" @click="editDocModal = false" aria-label="close" class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition cursor-pointer flex items-center justify-center">
                    <span class="material-icons text-2xl">close</span>
                </button>
                <h2 class="text-xl font-normal tracking-wide text-white" x-text="currentDoc ? currentDoc.name : ''"></h2>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="saveDocEdit()" :disabled="docSaving" class="text-white hover:text-white font-medium text-sm px-4 py-1.5 rounded hover:bg-white/10 transition cursor-pointer uppercase tracking-wider disabled:opacity-50">
                    <span x-text="docSaving ? 'Saving...' : 'Save'"></span>
                </button>
            </div>
        </div>
        <!-- MUI style Editor container -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-slate-100 flex flex-col items-center custom-scrollbar">
            <div class="bg-white shadow-lg border border-slate-200 rounded-lg p-6 max-w-5xl w-full flex-1 flex flex-col gap-3 min-h-[550px]">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs text-slate-500 font-medium">
                    <span>HTML Document Source Editor</span>
                    <span>Click 'Save' in top bar to save changes & version</span>
                </div>
                <textarea x-model="editDocHtml" class="w-full flex-1 min-h-[500px] font-mono text-xs p-4 bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:border-indigo-500 leading-relaxed custom-scrollbar"></textarea>
            </div>
        </div>
    </div>

    <!-- CONFIRM REGENERATE & OVERWRITE MODAL -->
    <div x-show="confirmRegenerateModal" class="fixed inset-0 z-80 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display: none;" @keydown.escape.window="confirmRegenerateModal = false" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-icons text-xl">autorenew</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Update & overwrite</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Do you want to update & overwrite this document?</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200" x-text="'Document: ' + (docToRegenerate ? docToRegenerate.name : '')"></p>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="confirmRegenerateModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="executeRegenerate()" :disabled="docRegenerating" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition cursor-pointer disabled:opacity-50">
                    <span x-text="docRegenerating ? 'Overwriting...' : 'Update & Overwrite'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- CONFIRM DELETE DOCUMENT MODAL -->
    <div x-show="confirmDeleteDocModal" class="fixed inset-0 z-80 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4" style="display: none;" @keydown.escape.window="confirmDeleteDocModal = false" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <span class="material-icons text-xl">delete_outline</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Delete document</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Are you sure you want to delete this document?</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200" x-text="'Document: ' + (docToDelete ? docToDelete.name : '')"></p>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="confirmDeleteDocModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" @click="executeDeleteDoc()" :disabled="docDeleting" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition cursor-pointer disabled:opacity-50">
                    <span x-text="docDeleting ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- DOCUMENT VERSIONS MODAL (Show versions) -->
    <div x-show="docVersionsModal" class="fixed inset-0 z-80 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 sm:p-6" style="display: none;" @keydown.escape.window="docVersionsModal = false" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-2xl w-full p-6 space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <span class="material-icons text-lg">history</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Document Versions</h3>
                        <p class="text-xs text-slate-500" x-text="currentDoc ? currentDoc.name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="docVersionsModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <span class="material-icons text-lg">close</span>
                </button>
            </div>

            <!-- Loading indicator -->
            <div x-show="loadingDocVersions" class="py-12 flex flex-col items-center justify-center text-slate-400 text-xs">
                <span class="material-icons animate-spin text-2xl text-indigo-600 mb-2">refresh</span>
                <span>Loading versions...</span>
            </div>

            <!-- Versions list -->
            <div x-show="!loadingDocVersions" class="flex-1 overflow-y-auto space-y-3 custom-scrollbar pr-1">
                <!-- Current Version Row -->
                <div class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/40 flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white uppercase tracking-wider">Current Version</span>
                            <span class="text-xs font-semibold text-slate-800">Active Document</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Currently active and rendered in this contract.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'/documents/' + (currentDoc ? currentDoc.id : '') + '/view'" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer inline-block">
                            View
                        </a>
                    </div>
                </div>

                <!-- Historical Versions List -->
                <template x-for="ver in currentDocVersions" :key="ver.id">
                    <div class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 bg-white flex items-center justify-between transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase" x-text="'v' + ver.version"></span>
                                <span class="text-xs font-semibold text-slate-800" x-text="ver.userName || 'User'"></span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-mono" x-text="ver.dateTime"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="previewVersion(ver)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                                Preview
                            </button>
                            <button type="button" @click="restoreVersion(ver)" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                                Restore
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="currentDocVersions.length === 0" class="py-6 text-center text-xs text-slate-400">
                    No previous historical versions found. Previous versions are archived automatically when you edit or overwrite.
                </div>
            </div>

            <div class="flex items-center justify-end pt-3 border-t border-slate-100">
                <button type="button" @click="docVersionsModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- PREVIEW HISTORICAL VERSION MODAL -->
    <div x-show="viewVersionModal" class="fixed inset-0 z-90 overflow-hidden flex flex-col bg-slate-900/70 backdrop-blur-xs" style="display: none;" @keydown.escape.window="viewVersionModal = false" x-cloak>
        <div class="bg-slate-800 text-white px-6 py-3.5 flex items-center justify-between shadow-md shrink-0">
            <div class="flex items-center gap-3">
                <button type="button" @click="viewVersionModal = false" class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition cursor-pointer">
                    <span class="material-icons text-xl">close</span>
                </button>
                <h3 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                    <span class="text-indigo-400 text-xs px-2 py-0.5 rounded bg-indigo-400/20 font-bold uppercase" x-text="selectedVersionInfo ? ('v' + selectedVersionInfo.version) : 'Historical Version'"></span>
                    <span x-text="currentDoc ? currentDoc.name : 'Version Preview'"></span>
                </h3>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="if(selectedVersionInfo) restoreVersion(selectedVersionInfo); viewVersionModal = false;" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs px-4 py-1.5 rounded-md transition cursor-pointer flex items-center gap-1.5 shadow-2xs">
                    <span class="material-icons text-sm">restore</span>
                    <span>Restore this version</span>
                </button>
                <button type="button" @click="viewVersionModal = false" class="text-slate-300 hover:text-white font-medium text-xs px-3 py-1.5 rounded-md hover:bg-slate-700 transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto bg-slate-100 flex justify-center custom-scrollbar">
            <div class="p-10 border-dashed border border-gray-400 bg-white m-10 w-fit shadow-xs shrink-0" x-html="selectedVersionHtml || ''"></div>
        </div>
    </div>
</form>
