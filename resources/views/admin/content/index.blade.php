@extends('layouts.app')

@section('title', 'Content Management')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Content Management</h1>
            <p class="text-dark-400 mt-1">Manage landing page content, testimonials, FAQs, and footer links</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-4 text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-4 text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Content Tabs -->
    <div x-data="{ activeTab: '{{ request('tab', 'landing') }}' }" class="space-y-6">
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 border-b border-dark-700 pb-4">
            <button @click="activeTab = 'landing'" 
                    :class="activeTab === 'landing' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Landing Page
            </button>
            <button @click="activeTab = 'testimonials'" 
                    :class="activeTab === 'testimonials' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Testimonials
            </button>
            <button @click="activeTab = 'faqs'" 
                    :class="activeTab === 'faqs' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                FAQs
            </button>
            <button @click="activeTab = 'footer'" 
                    :class="activeTab === 'footer' ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-300 hover:bg-dark-600'"
                    class="px-4 py-2 rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                </svg>
                Footer Links
            </button>
        </div>

        <!-- Landing Page Content Tab -->
        <div x-show="activeTab === 'landing'" x-cloak>
            <div class="grid lg:grid-cols-2 gap-6">
                @foreach($siteContentGrouped as $section => $contents)
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white capitalize">
                            {{ \App\Models\SiteContent::SECTIONS[$section] ?? ucfirst(str_replace('_', ' ', $section)) }}
                        </h3>
                        <span class="text-xs text-dark-400">{{ count($contents) }} items</span>
                    </div>
                    
                        <form action="{{ route('admin.content.section.update', $section) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        
                        @foreach($contents as $content)
                        <div>
                            <label class="block text-sm font-medium text-dark-300 mb-1">
                                {{ $content->label }}
                                @if($content->is_required)
                                    <span class="text-red-400">*</span>
                                @endif
                            </label>
                            
                            @if($content->description)
                                <p class="text-xs text-dark-500 mb-1">{{ $content->description }}</p>
                            @endif
                            
                            @if($content->type === 'html')
                                <textarea name="content[{{ $content->key }}]" rows="4" 
                                          class="input-field w-full text-sm font-mono">{{ $content->value }}</textarea>
                            @elseif($content->type === 'json')
                                <textarea name="content[{{ $content->key }}]" rows="6" 
                                          class="input-field w-full text-sm font-mono"
                                          placeholder="JSON format">{{ is_array($content->getValue()) ? json_encode($content->getValue(), JSON_PRETTY_PRINT) : $content->value }}</textarea>
                                <p class="text-xs text-dark-500 mt-1">Edit JSON carefully. Invalid JSON will be rejected.</p>
                            @else
                                <input type="text" name="content[{{ $content->key }}]" 
                                       value="{{ $content->value }}"
                                       class="input-field w-full text-sm"
                                       @if($content->is_required) required @endif>
                            @endif
                        </div>
                        @endforeach
                        
                        <div class="pt-4 border-t border-dark-700">
                            <button type="submit" class="btn-primary w-full">
                                Save {{ \App\Models\SiteContent::SECTIONS[$section] ?? ucfirst($section) }} Content
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
            
            @if(empty($siteContentGrouped) || count($siteContentGrouped) === 0)
                <div class="card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">No Content Found</h3>
                    <p class="text-dark-400 mb-4">Run the content seeder to initialize landing page content.</p>
                    <form action="{{ route('admin.content.seed') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary">
                            Initialize Landing Page Content
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Testimonials Tab -->
        <div x-show="activeTab === 'testimonials'" x-cloak>
            <div class="flex justify-end mb-4">
                <button type="button" 
                        onclick="document.getElementById('addTestimonialModal').classList.remove('hidden')"
                        class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Testimonial
                </button>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($testimonials ?? [] as $testimonial)
                <div class="card p-6 relative">
                    @if($testimonial->is_featured)
                        <span class="absolute top-2 right-2 bg-yellow-500/20 text-yellow-400 text-xs px-2 py-1 rounded">Featured</span>
                    @endif
                    
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ $testimonial->avatar_url }}" alt="{{ $testimonial->name }}" 
                             class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <h4 class="text-white font-medium">{{ $testimonial->name }}</h4>
                            <p class="text-dark-400 text-sm">{{ $testimonial->role }}</p>
                            @if($testimonial->company)
                                <p class="text-dark-500 text-xs">{{ $testimonial->company }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <p class="text-dark-300 text-sm italic mb-4">"{{ Str::limit($testimonial->quote, 150) }}"</p>
                    
                    <div class="flex items-center justify-between">
                        <div class="text-yellow-400 text-sm">
                            {!! $testimonial->stars_html !!}
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button type="button" 
                                    onclick="editTestimonial('{{ $testimonial->id }}')"
                                    class="text-dark-400 hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form action="{{ route('admin.content.testimonials.toggle', $testimonial) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="{{ $testimonial->is_active ? 'text-green-400' : 'text-dark-500' }} hover:text-white transition-colors"
                                        title="{{ $testimonial->is_active ? 'Active' : 'Inactive' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </form>
                            <form action="{{ route('admin.content.testimonials.destroy', $testimonial) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this testimonial?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-dark-400 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">No Testimonials</h3>
                    <p class="text-dark-400">Add testimonials to display on the landing page.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- FAQs Tab -->
        <div x-show="activeTab === 'faqs'" x-cloak>
            <div class="flex justify-end mb-4">
                <button type="button" 
                        onclick="document.getElementById('addFaqModal').classList.remove('hidden')"
                        class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add FAQ
                </button>
            </div>

            @foreach($faqsGrouped ?? [] as $category => $categoryData)
            <div class="card p-6 mb-6">
                <h3 class="text-lg font-semibold text-white mb-4">{{ $categoryData['label'] }}</h3>
                
                <div class="space-y-4">
                    @foreach($categoryData['faqs'] as $faq)
                    <div class="bg-dark-700/50 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-white font-medium">{{ $faq->question }}</h4>
                                    @if($faq->is_featured)
                                        <span class="bg-yellow-500/20 text-yellow-400 text-xs px-2 py-0.5 rounded">Featured</span>
                                    @endif
                                </div>
                                <div class="text-dark-300 text-sm prose prose-sm prose-invert max-w-none">
                                    {!! Str::limit(strip_tags($faq->answer), 200) !!}
                                </div>
                                <p class="text-dark-500 text-xs mt-2">{{ $faq->view_count }} views</p>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                <form action="{{ route('admin.content.faqs.toggle', $faq) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="{{ $faq->is_active ? 'text-green-400' : 'text-dark-500' }} hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('admin.content.faqs.destroy', $faq) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this FAQ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-dark-400 hover:text-red-400 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            @if(empty($faqsGrouped))
                <div class="card p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-dark-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">No FAQs</h3>
                    <p class="text-dark-400">Add frequently asked questions.</p>
                </div>
            @endif
        </div>

        <!-- Footer Links Tab -->
        <div x-show="activeTab === 'footer'" x-cloak>
            <div class="flex justify-end mb-4">
                <button type="button" 
                        onclick="document.getElementById('addFooterLinkModal').classList.remove('hidden')"
                        class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Link
                </button>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                @foreach(\App\Models\FooterLink::COLUMNS as $column => $columnLabel)
                <div class="card p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">{{ $columnLabel }}</h3>
                    
                    <ul class="space-y-2">
                        @foreach(($footerLinks[$column]['links'] ?? []) as $link)
                        <li class="flex items-center justify-between group">
                            <span class="text-dark-300 text-sm {{ !$link->is_active ? 'line-through opacity-50' : '' }}">
                                @if($link->icon)
                                    <span class="mr-1">{{ $link->icon }}</span>
                                @endif
                                {{ $link->label }}
                            </span>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1">
                                <form action="{{ route('admin.content.footer-links.destroy', $link) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this link?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-dark-400 hover:text-red-400 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    
                    @if(($footerLinks[$column]['links'] ?? collect())->isEmpty())
                        <p class="text-dark-500 text-sm">No links in this column</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Add Testimonial Modal -->
<div id="addTestimonialModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-dark-800 rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-dark-700">
            <h3 class="text-lg font-semibold text-white">Add Testimonial</h3>
        </div>
        <form action="{{ route('admin.content.testimonials.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Name *</label>
                    <input type="text" name="name" required class="input-field w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Role *</label>
                    <input type="text" name="role" required class="input-field w-full" placeholder="e.g., Full-Stack Developer">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Company</label>
                <input type="text" name="company" class="input-field w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Testimonial Quote *</label>
                <textarea name="quote" rows="4" required class="input-field w-full" placeholder="What they said about the program..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Image URL</label>
                    <input type="url" name="image_url" class="input-field w-full" placeholder="https://...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Rating (1-5)</label>
                    <select name="rating" class="input-field w-full">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" class="mr-2">
                    <span class="text-dark-300 text-sm">Featured</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                    <span class="text-dark-300 text-sm">Active</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
                <button type="button" 
                        onclick="document.getElementById('addTestimonialModal').classList.add('hidden')"
                        class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Add Testimonial</button>
            </div>
        </form>
    </div>
</div>

<!-- Add FAQ Modal -->
<div id="addFaqModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-dark-800 rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-dark-700">
            <h3 class="text-lg font-semibold text-white">Add FAQ</h3>
        </div>
        <form action="{{ route('admin.content.faqs.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Category *</label>
                <select name="category" required class="input-field w-full">
                    @foreach(\App\Models\FAQ::CATEGORIES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Question *</label>
                <input type="text" name="question" required class="input-field w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Answer * (HTML supported)</label>
                <textarea name="answer" rows="6" required class="input-field w-full font-mono text-sm" placeholder="<p>Your answer here...</p>"></textarea>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" class="mr-2">
                    <span class="text-dark-300 text-sm">Featured on Landing</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                    <span class="text-dark-300 text-sm">Active</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
                <button type="button" 
                        onclick="document.getElementById('addFaqModal').classList.add('hidden')"
                        class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Add FAQ</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Footer Link Modal -->
<div id="addFooterLinkModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-dark-800 rounded-xl shadow-xl max-w-lg w-full">
        <div class="p-6 border-b border-dark-700">
            <h3 class="text-lg font-semibold text-white">Add Footer Link</h3>
        </div>
        <form action="{{ route('admin.content.footer-links.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Column *</label>
                    <select name="column" required class="input-field w-full">
                        @foreach(\App\Models\FooterLink::COLUMNS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Label *</label>
                    <input type="text" name="label" required class="input-field w-full">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">URL</label>
                <input type="text" name="url" class="input-field w-full" placeholder="/about or https://...">
            </div>
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Icon (optional)</label>
                <input type="text" name="icon" class="input-field w-full" placeholder="twitter, linkedin, etc.">
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_external" value="1" class="mr-2">
                    <span class="text-dark-300 text-sm">External Link</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="open_new_tab" value="1" class="mr-2">
                    <span class="text-dark-300 text-sm">Open in New Tab</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-dark-700">
                <button type="button" 
                        onclick="document.getElementById('addFooterLinkModal').classList.add('hidden')"
                        class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Add Link</button>
            </div>
        </form>
    </div>
</div>

@endsection
