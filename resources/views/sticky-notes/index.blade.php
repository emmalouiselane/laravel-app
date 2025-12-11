@extends('layouts.app')

@section('title', 'Sticky Notes')

@push('styles')
<style>
    .sticky-notes-container {
        margin: 0 auto;
        padding: 1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    
    .sticky-notes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .sticky-notes-board {
        flex: 1;
        position: relative;
        background: #f9fafb;
        border: 2px dashed #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .sticky-note {
        position: absolute;
        background: #FEF3C7;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        cursor: move;
        transition: box-shadow 0.2s, transform 0.2s;
        min-width: 150px;
        min-height: 150px;
        max-width: 500px;
        max-height: 500px;
        overflow: hidden;
    }
    
    .sticky-note:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }
    
    .sticky-note.dragging {
        opacity: 0.5;
        cursor: grabbing;
    }
    
    .sticky-note.pinned {
        border-color: #e5e7eb;
        border-width: 1px;
    }
    
    .sticky-note-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .sticky-note-title {
        font-weight: 600;
        font-size: 0.875rem;
        color: #1f2937;
        flex: 1;
        margin-right: 0.5rem;
        word-wrap: break-word;
    }
    
    .sticky-note-content {
        font-size: 0.875rem;
        color: #4b5563;
        line-height: 1.4;
        word-wrap: break-word;
        white-space: pre-wrap;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .sticky-note-actions {
        display: flex;
        gap: 0.25rem;
        opacity: 0.3;
        transition: opacity 0.2s ease;
    }
    
    .sticky-note:hover .sticky-note-actions,
    .sticky-note:focus .sticky-note-actions {
        opacity: 1;
    }
    
    /* Always show actions on mobile */
    @media (max-width: 768px) {
        .sticky-note-actions {
            opacity: 0.6;
        }
    }
    
    .sticky-note-btn {
        width: 24px;
        height: 24px;
        border: none;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    
    .sticky-note-btn:hover {
        background: rgba(0, 0, 0, 0.2);
    }
    
    .add-sticky-note-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        transition: background 0.2s, transform 0.2s;
        z-index: 1000;
    }
    
    .add-sticky-note-btn:hover {
        background: #2563eb;
        transform: scale(1.05);
    }
    
    .sticky-note-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }
    
    .sticky-note-modal.hidden {
        display: none !important;
    }
    
    .sticky-note-form {
        background: white;
        border-radius: 0.5rem;
        padding: 1.5rem;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .sticky-note-form button {
        display: inline-block !important;
        visibility: visible !important;
    }
    
    .color-picker {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .color-option {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    
    .color-option:hover {
        border-color: #1f2937;
    }
    
    .color-option.selected {
        border-color: #1f2937;
        box-shadow: 0 0 0 2px white, 0 0 0 4px #1f2937;
    }
    
    .resize-handle {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 20px;
        height: 20px;
        cursor: nwse-resize;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .sticky-note:hover .resize-handle {
        opacity: 0.5;
    }
    
    .resize-handle::before {
        content: '';
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #9ca3af;
        border-bottom: 2px solid #9ca3af;
    }
</style>
@endpush

@section('content')
<div class="container sticky-notes-container">
    <header class="sticky-notes-header">
        <h1 class="text-2xl font-bold">Sticky Notes</h1>
        <div class="flex items-center gap-4">
            <button type="button" onclick="openAddModal()" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                Add Sticky
            </button>
            <x-auth-buttons 
                :showDashboard="true"
                :showAccount="true"
                :showLogout="true"
            />
        </div>
    </header>
    
    <div class="sticky-notes-board" id="stickyNotesBoard">
        @foreach($stickyNotes as $stickyNote)
            <div class="sticky-note" 
                 data-id="{{ $stickyNote->id }}"
                 style="background-color: {{ $stickyNote->color }}; 
                        left: {{ $stickyNote->position_x }}px; 
                        top: {{ $stickyNote->position_y }}px;
                        width: {{ $stickyNote->width }}px;
                        height: {{ $stickyNote->height }}px;">
                <div class="sticky-note-header">
                    <div class="sticky-note-title" contenteditable="false" data-field="title">{{ $stickyNote->title }}</div>
                    <div class="sticky-note-actions">
                        <button type="button" class="sticky-note-btn" onclick="editStickyNote({{ $stickyNote->id }})" title="Edit">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button type="button" class="sticky-note-btn" onclick="deleteStickyNote({{ $stickyNote->id }})" title="Delete">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1H3M9 7h6m-6 10h6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="sticky-note-content" contenteditable="false" data-field="content">{{ $stickyNote->content }}</div>
                <div class="resize-handle"></div>
            </div>
        @endforeach
    </div>

<!-- Add/Edit Sticky Note Modal -->
<div id="stickyNoteModal" class="sticky-note-modal hidden">
    <div class="sticky-note-form">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Add Sticky Note</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                <x-bladewind::icon name="x-mark" class="size-5" />
            </button>
        </div>
        
        <form id="stickyNoteForm">
            <div class="space-y-4">
                <div>
                    <label for="noteTitle" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" 
                           id="noteTitle" 
                           name="title" 
                           required
                           maxlength="255"
                           class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="noteContent" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea id="noteContent" 
                              name="content" 
                              rows="4"
                              maxlength="2000"
                              class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <div class="color-picker">
                        <div class="color-option selected" style="background-color: #FEF3C7;" data-color="#FEF3C7"></div>
                        <div class="color-option" style="background-color: #FEE2E2;" data-color="#FEE2E2"></div>
                        <div class="color-option" style="background-color: #DBEAFE;" data-color="#DBEAFE"></div>
                        <div class="color-option" style="background-color: #D1FAE5;" data-color="#D1FAE5"></div>
                        <div class="color-option" style="background-color: #E9D5FF;" data-color="#E9D5FF"></div>
                        <div class="color-option" style="background-color: #FED7AA;" data-color="#FED7AA"></div>
                        <div class="color-option" style="background-color: #F3F4F6;" data-color="#F3F4F6"></div>
                        <div class="color-option" style="background-color: #FEF9C3;" data-color="#FEF9C3"></div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Add Note
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Global variables
    let selectedColor = '#FEF3C7';
    let editingNoteId = null;
    let draggedNote = null;
    let resizingNote = null;
    let startPosX = 0;
    let startPosY = 0;
    let startWidth = 0;
    let startHeight = 0;

    // Shared icon templates
    const icons = {
        edit: `<svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>`,
        delete: `<svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1H3M9 7h6m-6 10h6" />
        </svg>`
    };

    // Color picker
    document.querySelectorAll('.color-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            selectedColor = this.dataset.color;
        });
    });

    // Modal functions
    function openAddModal() {
        editingNoteId = null;
        document.getElementById('stickyNoteForm').reset();
        document.querySelector('#stickyNoteModal h3').textContent = 'Add Sticky Note';
        document.querySelector('#stickyNoteModal button[type="submit"]').textContent = 'Add Note';
        document.getElementById('stickyNoteModal').classList.remove('hidden');
    }

    function closeModal() {
        const modal = document.getElementById('stickyNoteModal');
        modal.classList.add('hidden');
        editingNoteId = null;
    }

    // Form submission
    document.getElementById('stickyNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            title: formData.get('title'),
            content: formData.get('content'),
            color: selectedColor,
            position_x: getRandomPositionX(),
            position_y: getRandomPositionY(),
            width: 250,
            height: 250
        };

        // Helper functions for mobile-friendly positioning
        function getRandomPositionX() {
            const board = document.getElementById('stickyNotesBoard');
            const boardRect = board.getBoundingClientRect();
            const maxPosition = Math.max(0, boardRect.width - 280); // 280 = note width + margin
            return Math.floor(Math.random() * Math.min(maxPosition, 300)); // Cap at 300px for better mobile experience
        }

        function getRandomPositionY() {
            const board = document.getElementById('stickyNotesBoard');
            const boardRect = board.getBoundingClientRect();
            const maxPosition = Math.max(0, boardRect.height - 280); // 280 = note height + margin
            return Math.floor(Math.random() * Math.min(maxPosition, 200)); // Cap at 200px for better mobile experience
        }

        // Validate data before sending
        if (!data.title || data.title.trim() === '') {
            alert('Title is required');
            return;
        }
        
        if (!data.color || !data.color.match(/^#[0-9A-Fa-f]{6}$/)) {
            alert('Invalid color format: ' + data.color);
            return;
        }

        const url = editingNoteId ? `/sticky-notes/${editingNoteId}` : '/sticky-notes';
        const method = editingNoteId ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 422) {
                    // Handle validation errors
                    return response.json().then(errors => {
                        const errorMessage = errors.errors ? Object.values(errors.errors).flat().join(', ') : 'Validation error';
                        throw new Error(errorMessage);
                    });
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (editingNoteId) {
                updateNoteInDOM(data);
            } else {
                addNoteToDOM(data);
            }
            closeModal();
        })
        .catch(error => {
            alert('Error saving sticky note: ' + error.message);
        });
    });

    function addNoteToDOM(note) {
        const board = document.getElementById('stickyNotesBoard');
        const noteElement = createNoteElement(note);
        
        // Auto-resize to fit content
        setTimeout(() => {
            resizeNoteToContent(noteElement);
        }, 10);
        
        board.appendChild(noteElement);
    }

    function resizeNoteToContent(noteElement) {
        const titleElement = noteElement.querySelector('[data-field="title"]');
        const contentElement = noteElement.querySelector('[data-field="content"]');
        
        // Calculate content height
        const titleHeight = titleElement ? titleElement.offsetHeight : 0;
        const contentHeight = contentElement ? contentElement.scrollHeight : 0;
        const padding = 40; // Account for padding and header
        const minHeight = 150;
        
        const newHeight = Math.max(minHeight, titleHeight + contentHeight + padding);
        const newWidth = Math.max(200, Math.min(400, noteElement.offsetWidth));
        
        noteElement.style.width = newWidth + 'px';
        noteElement.style.height = newHeight + 'px';
        
        // Save new dimensions to database
        const noteId = noteElement.dataset.id;
        if (noteId) {
            fetch(`/sticky-notes/${noteId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    width: newWidth,
                    height: newHeight
                })
            });
        }
    }

    function updateNoteInDOM(note) {
        const noteElement = document.querySelector(`[data-id="${note.id}"]`);
        if (noteElement) {
            noteElement.style.backgroundColor = note.color;
            noteElement.querySelector('[data-field="title"]').textContent = note.title;
            noteElement.querySelector('[data-field="content"]').textContent = note.content;
        }
    }

    // Shared template function for sticky notes
    function createStickyNoteHTML(note) {
        return `
            <div class="sticky-note" 
                 data-id="${note.id}"
                 style="background-color: ${note.color}; 
                        left: ${note.position_x}px; 
                        top: ${note.position_y}px;
                        width: ${note.width}px;
                        height: ${note.height}px;">
                <div class="sticky-note-header">
                    <div class="sticky-note-title" contenteditable="false" data-field="title">${note.title}</div>
                    <div class="sticky-note-actions">
                        <button type="button" class="sticky-note-btn" onclick="editStickyNote(${note.id})" title="Edit">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button type="button" class="sticky-note-btn" onclick="deleteStickyNote(${note.id})" title="Delete">
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1H3M9 7h6m-6 10h6" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="sticky-note-content" contenteditable="false" data-field="content">${note.content}</div>
                <div class="resize-handle"></div>
            </div>
        `;
    }

    function createNoteElement(note) {
        const div = document.createElement('div');
        div.innerHTML = createStickyNoteHTML(note);
        const noteElement = div.firstElementChild;
        
        setupNoteInteractions(noteElement);
        return noteElement;
    }

    function setupNoteInteractions(noteElement) {
        // Dragging - Mouse events
        noteElement.addEventListener('mousedown', function(e) {
            if (e.target.classList.contains('resize-handle')) return;
            if (e.target.classList.contains('sticky-note-btn')) return;
            
            draggedNote = this;
            startPosX = e.clientX - parseInt(this.style.left);
            startPosY = e.clientY - parseInt(this.style.top);
            this.classList.add('dragging');
            e.preventDefault();
        });

        // Dragging - Touch events for mobile
        noteElement.addEventListener('touchstart', function(e) {
            if (e.target.classList.contains('resize-handle')) return;
            if (e.target.classList.contains('sticky-note-btn')) return;
            
            const touch = e.touches[0];
            draggedNote = this;
            startPosX = touch.clientX - parseInt(this.style.left);
            startPosY = touch.clientY - parseInt(this.style.top);
            this.classList.add('dragging');
            e.preventDefault();
        }, { passive: false });

        // Resizing - Mouse events
        const resizeHandle = noteElement.querySelector('.resize-handle');
        resizeHandle.addEventListener('mousedown', function(e) {
            resizingNote = noteElement;
            startWidth = parseInt(noteElement.style.width);
            startHeight = parseInt(noteElement.style.height);
            startPosX = e.clientX;
            startPosY = e.clientY;
            e.preventDefault();
            e.stopPropagation();
        });

        // Resizing - Touch events for mobile
        resizeHandle.addEventListener('touchstart', function(e) {
            const touch = e.touches[0];
            resizingNote = noteElement;
            startWidth = parseInt(noteElement.style.width);
            startHeight = parseInt(noteElement.style.height);
            startPosX = touch.clientX;
            startPosY = touch.clientY;
            e.preventDefault();
            e.stopPropagation();
        }, { passive: false });

        // Content editing
        const titleElement = noteElement.querySelector('[data-field="title"]');
        const contentElement = noteElement.querySelector('[data-field="content"]');
        
        [titleElement, contentElement].forEach(element => {
            element.addEventListener('dblclick', function() {
                this.contentEditable = true;
                this.focus();
                selectAll(this);
            });

            element.addEventListener('blur', function() {
                this.contentEditable = false;
                saveNoteContent(noteElement.dataset.id, this.dataset.field, this.textContent);
            });

            element.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.blur();
                }
            });
        });
    }

    // Setup existing notes
    document.querySelectorAll('.sticky-note').forEach(setupNoteInteractions);

    // Global mouse events
    document.addEventListener('mousemove', function(e) {
        if (draggedNote) {
            const board = document.getElementById('stickyNotesBoard');
            const boardRect = board.getBoundingClientRect();
            
            let newX = e.clientX - startPosX;
            let newY = e.clientY - startPosY;
            
            // Keep within bounds
            newX = Math.max(0, Math.min(newX, boardRect.width - draggedNote.offsetWidth));
            newY = Math.max(0, Math.min(newY, boardRect.height - draggedNote.offsetHeight));
            
            draggedNote.style.left = newX + 'px';
            draggedNote.style.top = newY + 'px';
        }
        
        if (resizingNote) {
            const newWidth = startWidth + (e.clientX - startPosX);
            const newHeight = startHeight + (e.clientY - startPosY);
            
            resizingNote.style.width = Math.max(150, Math.min(500, newWidth)) + 'px';
            resizingNote.style.height = Math.max(150, Math.min(500, newHeight)) + 'px';
        }
    });

    // Global touch events for mobile
    document.addEventListener('touchmove', function(e) {
        if (draggedNote) {
            const touch = e.touches[0];
            const board = document.getElementById('stickyNotesBoard');
            const boardRect = board.getBoundingClientRect();
            
            let newX = touch.clientX - startPosX;
            let newY = touch.clientY - startPosY;
            
            // Keep within bounds
            newX = Math.max(0, Math.min(newX, boardRect.width - draggedNote.offsetWidth));
            newY = Math.max(0, Math.min(newY, boardRect.height - draggedNote.offsetHeight));
            
            draggedNote.style.left = newX + 'px';
            draggedNote.style.top = newY + 'px';
            e.preventDefault();
        }
        
        if (resizingNote) {
            const touch = e.touches[0];
            const newWidth = startWidth + (touch.clientX - startPosX);
            const newHeight = startHeight + (touch.clientY - startPosY);
            
            resizingNote.style.width = Math.max(150, Math.min(500, newWidth)) + 'px';
            resizingNote.style.height = Math.max(150, Math.min(500, newHeight)) + 'px';
            e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('mouseup', function() {
        if (draggedNote) {
            draggedNote.classList.remove('dragging');
            saveNotePosition(draggedNote);
            draggedNote = null;
        }
        
        if (resizingNote) {
            saveNoteSize(resizingNote);
            resizingNote = null;
        }
    });

    // Global touch end events for mobile
    document.addEventListener('touchend', function(e) {
        if (draggedNote) {
            draggedNote.classList.remove('dragging');
            saveNotePosition(draggedNote);
            draggedNote = null;
        }
        
        if (resizingNote) {
            saveNoteSize(resizingNote);
            resizingNote = null;
        }
    });

    function saveNotePosition(noteElement) {
        const noteId = noteElement.dataset.id;
        const positionX = parseInt(noteElement.style.left);
        const positionY = parseInt(noteElement.style.top);
        
        fetch(`/sticky-notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                position_x: positionX,
                position_y: positionY
            })
        });
    }

    function saveNoteSize(noteElement) {
        const noteId = noteElement.dataset.id;
        const width = parseInt(noteElement.style.width);
        const height = parseInt(noteElement.style.height);
        
        fetch(`/sticky-notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                width: width,
                height: height
            })
        });
    }

    function saveNoteContent(noteId, field, content) {
        fetch(`/sticky-notes/${noteId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                [field]: content
            })
        });
    }

    function togglePin(noteId) {
        fetch(`/sticky-notes/${noteId}/toggle-pin`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            const noteElement = document.querySelector(`[data-id="${noteId}"]`);
            if (data.is_pinned) {
                noteElement.classList.add('pinned');
                noteElement.querySelector('.sticky-note-btn').innerHTML = `
                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                `;
            } else {
                noteElement.classList.remove('pinned');
                noteElement.querySelector('.sticky-note-btn').innerHTML = `
                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 2a2 2 0 012 2v6a2 2 0 01-2 2h-4v8l-4-2H6a2 2 0 01-2-2V4a2 2 0 012-2h9z" />
                    </svg>
                `;
            }
        })
        .catch(error => console.error('Error toggling pin:', error));
    }

    function editStickyNote(noteId) {
        const noteElement = document.querySelector(`[data-id="${noteId}"]`);
        const title = noteElement.querySelector('[data-field="title"]').textContent;
        const content = noteElement.querySelector('[data-field="content"]').textContent;
        const color = window.getComputedStyle(noteElement).backgroundColor;
        
        // Convert RGB to hex
        const hexColor = rgbToHex(color);
        
        editingNoteId = noteId;
        document.getElementById('noteTitle').value = title;
        document.getElementById('noteContent').value = content;
        
        // Select the correct color
        document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
        const colorOption = document.querySelector(`[data-color="${hexColor}"]`);
        if (colorOption) {
            colorOption.classList.add('selected');
            selectedColor = hexColor;
        }
        
        document.querySelector('#stickyNoteModal h3').textContent = 'Edit Sticky Note';
        document.querySelector('#stickyNoteModal button[type="submit"]').textContent = 'Update Note';
        document.getElementById('stickyNoteModal').classList.remove('hidden');
    }

    function rgbToHex(rgb) {
        const result = rgb.match(/\d+/g);
        if (!result) return '#FEF3C7';
        return "#" + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
    }

    function deleteStickyNote(noteId) {
        if (confirm('Are you sure you want to delete this sticky note?')) {
            fetch(`/sticky-notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(() => {
                const noteElement = document.querySelector(`[data-id="${noteId}"]`);
                noteElement.remove();
            });
        }
    }

    function selectAll(element) {
        const range = document.createRange();
        range.selectNodeContents(element);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }

    // Close modal on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    // Close modal on background click
    document.getElementById('stickyNoteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endpush
@endsection
