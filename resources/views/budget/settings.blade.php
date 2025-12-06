@extends('layouts.app')

@section('title', 'Budget Settings')

@push('styles')
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #eee;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #eee;
        }

        .label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .input {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
        }

        .tab-button {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            color: #6b7280;
            transition: all 0.2s;
        }

        .tab-button.active {
            color: var(--color-primary-500);
            border-bottom-color: var(--color-primary-500);
        }

        .tab-button:hover:not(.active) {
            color: var(--color-gray-500);
            border-bottom-color: var(--color-gray-200);
        }
    </style>
@endpush

@section('content')
    <div class="settings-container" x-data="{ 
                        activeTab: '{{ session('active_tab', 'general') }}',
                        categoryForm: {
                            id: null,
                            name: '',
                            color: '#3b82f6',
                            icon: ''
                        },
                        isEditing: false,
                        baseUpdateUrl: '{{ route('budget.categories.update', ['category' => 'QD_PLACEHOLDER']) }}',
                        
                        get updateActionUrl() {
                            return this.baseUpdateUrl.replace('QD_PLACEHOLDER', this.categoryForm.id);
                        },

                        resetForm() {
                            this.categoryForm = { id: null, name: '', color: '#3b82f6', icon: '' };
                            this.isEditing = false;
                        },

                        editCategory(data) {
                            this.categoryForm = { 
                                id: data.id, 
                                name: data.name, 
                                color: data.color, 
                                icon: data.icon 
                            };
                            this.isEditing = true;
                            // Scroll to form
                            const form = document.getElementById('category-form-card');
                            if(form) form.scrollIntoView({ behavior: 'smooth' });
                        }
                    }">
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Budget Settings</h1>
            <a href="{{ route('budget.index') }}"
                class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-700 hover:bg-gray-200">Back to Budget</a>
        </header>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="mb-6 border-b border-gray-200 flex">
            <button @click="activeTab = 'general'" :class="{ 'active': activeTab === 'general' }" class="tab-button">
                {{ __('General') }}
            </button>
            <button @click="activeTab = 'categories'" :class="{ 'active': activeTab === 'categories' }" class="tab-button">
                {{ __('Payment Categories') }}
            </button>
        </div>

        <!-- General Tab -->
        <div x-show="activeTab === 'general'" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Pay Period</h2>
                    <p class="text-sm text-gray-500">Choose weekly or monthly pay periods. For monthly, select the start day
                        of the month.</p>
                </div>
                <form action="{{ route('budget.settings.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="card-body space-y-4">
                        <div>
                            <label for="pay_period_mode" class="label">Pay period mode</label>
                            <select id="pay_period_mode" name="pay_period_mode" class="input max-w-40"
                                x-data="{ mode: '{{ $mode ?? 'monthly' }}' }" x-model="mode"
                                @change="document.getElementById('monthly_fields_wrapper').style.display = $el.value === 'monthly' ? 'block' : 'none'">
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                            </select>
                        </div>

                        <div id="monthly_fields_wrapper"
                            style="display: {{ ($mode ?? 'monthly') === 'monthly' ? 'block' : 'none' }}">
                            <label for="pay_period_start_day" class="label">Pay period start day</label>
                            <select id="pay_period_start_day" name="pay_period_start_day" class="input max-w-40">
                                @for ($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ (int) $startDay === $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="card-footer flex justify-end gap-2">
                        <x-bladewind::button can_submit="true" class="bg-primary-600 text-white hover:bg-primary-700">Save General Settings</x-bladewind::button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payment Categories Tab -->
        <div x-show="activeTab === 'categories'" style="display: none;" class="space-y-6">

            <!-- Add/Edit Category Form -->
            <div class="card" id="category-form-card">
                <div class="card-header">
                    <h2 class="font-semibold" x-text="isEditing ? 'Edit Category' : 'Add New Category'"></h2>
                </div>
                <form
                    :action="isEditing ? updateActionUrl : '{{ route('budget.categories.store') }}'"
                    method="POST">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PATCH">
                    </template>

                    <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="cat_name" class="label">Category Name</label>
                            <input type="text" id="cat_name" name="name" x-model="categoryForm.name" class="input" required
                                placeholder="e.g., Utilities, Groceries">
                        </div>
                        <div>
                            <label for="category_color" class="label">Color</label>
                            <input type="color" id="category_color" name="color" x-model="categoryForm.color"
                                class="h-10 w-full rounded cursor-pointer">
                        </div>

                        <!-- Icon Picker (Blade Loop) -->
                        <div class="md:col-span-2">
                            <label class="label">Icon (Optional)</label>
                            <input type="hidden" name="icon" x-model="categoryForm.icon">
                            <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 p-2 border rounded max-h-40 overflow-y-auto">
                                @foreach($icons as $icon)
                                    <button type="button"
                                        @click="categoryForm.icon = (categoryForm.icon === '{{ $icon }}' ? '' : '{{ $icon }}')"
                                        :class="{'bg-primary-100 ring-2 ring-primary-500': categoryForm.icon === '{{ $icon }}', 'hover:bg-gray-100': categoryForm.icon !== '{{ $icon }}'}"
                                        class="p-2 rounded flex items-center justify-center transition-colors">
                                        <x-bladewind::icon name="{{ $icon }}" class="size-6 text-gray-600" />
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1" x-show="categoryForm.icon">Selected: <span
                                    x-text="categoryForm.icon"></span></p>
                        </div>
                    </div>
                    <div class="card-footer flex justify-between items-center">
                        <div>
                            <button type="button" @click="resetForm()" x-show="isEditing"
                                class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel Edit</button>
                        </div>
                        <div>
                            <button type="submit"
                                class="px-3 py-1 rounded text-sm bg-primary-600 text-white hover:bg-primary-700"
                                x-text="isEditing ? 'Update Category' : 'Add Category'"></button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Categories List (Blade Loop) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="font-semibold">Existing Categories</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 border-b">
                            <tr>
                                <th class="px-4 py-3">Color</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($categories as $category)
                                <tr class="hover:bg-gray-50 group">
                                    <td class="px-4 py-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center border border-gray-200"
                                            style="background-color: {{ $category->color }}20; color: {{ $category->color }};">
                                            @if($category->icon)
                                                <x-bladewind::icon name="{{ $category->icon }}" class="size-5" />
                                            @else
                                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }};"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button 
                                                @click='editCategory(@json($category))'
                                                class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-700">
                                                Edit
                                            </button>
                                            <form action="{{ route('budget.categories.destroy', $category) }}" method="POST"
                                                onsubmit="return confirm('Are you sure? This cannot be undone if used in payments.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-2 py-1 text-xs bg-red-50 hover:bg-red-100 text-red-600 rounded">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-400">
                                        No categories found. Create one above!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection