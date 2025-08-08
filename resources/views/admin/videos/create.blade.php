<x-admin-layout title="Criar Vídeo" active-menu="videos" page-title="Criar Novo Vídeo">
    <x-slot name="styles">
        <style>
            .video-form-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.8) 100%);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 1.5rem;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                padding: 2rem;
                transition: all 0.3s ease;
            }
            
            .video-form-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            
            .form-label {
                display: block;
                font-weight: 600;
                color: #374151;
                margin-bottom: 0.5rem;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            
            .form-input {
                width: 100%;
                padding: 0.75rem 1rem;
                border: 2px solid #e5e7eb;
                border-radius: 0.75rem;
                font-size: 1rem;
                transition: all 0.3s ease;
                background: rgba(255, 255, 255, 0.8);
            }
            
            .form-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                background: rgba(255, 255, 255, 1);
            }
            
            .form-textarea {
                min-height: 120px;
                resize: vertical;
            }
            
            .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                color: white;
                padding: 0.875rem 2rem;
                border: none;
                border-radius: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            }
            
            .btn-secondary {
                background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
                color: white;
                padding: 0.875rem 2rem;
                border: none;
                border-radius: 0.75rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
            }
            
            .btn-secondary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(107, 114, 128, 0.3);
                color: white;
                text-decoration: none;
            }
        </style>
    </x-slot>

    <!-- Enhanced Page Header -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-6 mb-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    <svg class="w-8 h-8 mr-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <h2 class="text-3xl font-bold tracking-tight">🎬 Criar Novo Vídeo</h2>
                </div>
                <p class="text-blue-100 text-lg">Adicione um novo vídeo ao sistema de aprendizagem</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.videos') }}" class="btn-secondary group">
                    <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    ↩️ Voltar à Lista
                </a>
            </div>
        </div>
    </div>

    <!-- Video Creation Form -->
    <div class="max-w-4xl mx-auto">
        <div class="video-form-card">
            <form action="{{ route('admin.videos.store') }}" method="POST" id="videoForm">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="form-group md:col-span-2">
                        <label for="title" class="form-label">
                            📝 Título do Vídeo
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            class="form-input @error('title') border-red-500 @enderror" 
                            value="{{ old('title') }}"
                            placeholder="Digite o título do vídeo..."
                            required
                        >
                        @error('title')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- URL -->
                    <div class="form-group md:col-span-2">
                        <label for="url" class="form-label">
                            🔗 URL do Vídeo
                        </label>
                        <input 
                            type="url" 
                            id="url" 
                            name="url" 
                            class="form-input @error('url') border-red-500 @enderror" 
                            value="{{ old('url') }}"
                            placeholder="https://www.youtube.com/watch?v=..."
                            required
                        >
                        @error('url')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">Suporte para YouTube, Vimeo e outros serviços de vídeo</p>
                    </div>

                    <!-- Course Selection -->
                    @if(!empty($courses))
                    <div class="form-group">
                        <label for="course_id" class="form-label">
                            📚 Curso (Opcional)
                        </label>
                        <select 
                            id="course_id" 
                            name="course_id" 
                            class="form-input @error('course_id') border-red-500 @enderror"
                        >
                            <option value="">Selecione um curso...</option>
                            @foreach($courses as $course)
                                <option value="{{ $course['id'] ?? $course->id }}" {{ old('course_id') == ($course['id'] ?? $course->id) ? 'selected' : '' }}>
                                    {{ $course['title'] ?? $course->title ?? $course['name'] ?? $course->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Duration (Optional) -->
                    <div class="form-group">
                        <label for="duration" class="form-label">
                            ⏱️ Duração (Opcional)
                        </label>
                        <input 
                            type="text" 
                            id="duration" 
                            name="duration" 
                            class="form-input" 
                            value="{{ old('duration') }}"
                            placeholder="Ex: 15:30 ou 15 minutos"
                        >
                        <p class="text-gray-500 text-sm mt-1">Formato: MM:SS ou texto livre</p>
                    </div>

                    <!-- Description -->
                    <div class="form-group md:col-span-2">
                        <label for="description" class="form-label">
                            📄 Descrição
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-input form-textarea @error('description') border-red-500 @enderror"
                            placeholder="Descreva o conteúdo do vídeo, objetivos de aprendizagem, etc..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.videos') }}" class="btn-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Criar Vídeo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('videoForm');
                const urlInput = document.getElementById('url');
                
                // URL validation and preview
                urlInput.addEventListener('blur', function() {
                    const url = this.value;
                    if (url && !isValidVideoUrl(url)) {
                        this.classList.add('border-yellow-500');
                        showTooltip(this, 'Verifique se a URL é de um serviço de vídeo válido');
                    } else {
                        this.classList.remove('border-yellow-500');
                        hideTooltip(this);
                    }
                });
                
                function isValidVideoUrl(url) {
                    const videoPatterns = [
                        /youtube\.com\/watch\?v=/,
                        /youtu\.be\//,
                        /vimeo\.com\//,
                        /dailymotion\.com\//,
                        /wistia\.com\//
                    ];
                    
                    return videoPatterns.some(pattern => pattern.test(url));
                }
                
                function showTooltip(element, message) {
                    // Simple tooltip implementation
                    const existing = element.parentNode.querySelector('.tooltip');
                    if (existing) existing.remove();
                    
                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip text-yellow-600 text-sm mt-1';
                    tooltip.textContent = message;
                    element.parentNode.appendChild(tooltip);
                }
                
                function hideTooltip(element) {
                    const tooltip = element.parentNode.querySelector('.tooltip');
                    if (tooltip) tooltip.remove();
                }
                
                // Form submission with loading state
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Criando...
                    `;
                });
            });
        </script>
    </x-slot>
</x-admin-layout>
