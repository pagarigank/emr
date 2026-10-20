<?php

namespace App\Livewire\Philhealth;

use App\Models\Oecb\EeclItem;
use Livewire\Component;
use Livewire\WithPagination;

class EeclItemCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedCategory = '';
    public string $statusFilter = 'all'; // all, active, inactive
    public string $sortField = 'item_code';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $item_code = '';
    public string $category = 'Diagnostic Services';
    public string $subcategory = '';
    public string $item_name = '';
    public string $description = '';
    public string $unit = 'service';
    public string $fixed_fee = '0.00';
    public bool $is_active = true;

    public const CATEGORIES = [
        'Diagnostic Services',
        'ER Services',
        'Imaging Services',
        'Medicines',
        'Supplies',
    ];

    protected function rules(): array
    {
        $codeRule = ['required', 'string', 'max:30'];
        if ($this->isEditing && $this->editingId) {
            $codeRule[] = 'unique:eecl_items,item_code,' . $this->editingId . ',id,history,1';
        } else {
            $codeRule[] = 'unique:eecl_items,item_code,NULL,id,history,1';
        }

        return [
            'item_code' => $codeRule,
            'category' => ['required', 'string', 'in:' . implode(',', self::CATEGORIES)],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['nullable', 'string', 'max:50'],
            'fixed_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'item_code' => 'Item Code',
        'category' => 'Category',
        'subcategory' => 'Subcategory',
        'item_name' => 'Item Name',
        'fixed_fee' => 'Fixed Fee',
    ];

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = EeclItem::findOrFail($id);
        $this->editingId = $item->id;
        $this->item_code = $item->item_code;
        $this->category = $item->category;
        $this->subcategory = $item->subcategory ?? '';
        $this->item_name = $item->item_name;
        $this->description = $item->description ?? '';
        $this->unit = $item->unit ?? 'service';
        $this->fixed_fee = (string) $item->fixed_fee;
        $this->is_active = (bool) $item->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $uid = $this->currentUserId();
        $now = now();

        $data = [
            'item_code' => trim($validated['item_code']),
            'category' => $validated['category'],
            'subcategory' => $validated['subcategory'] ? trim($validated['subcategory']) : null,
            'item_name' => trim($validated['item_name']),
            'description' => $validated['description'] ? trim($validated['description']) : null,
            'unit' => $validated['unit'] ? trim($validated['unit']) : null,
            'fixed_fee' => (float) $validated['fixed_fee'],
            'is_active' => $validated['is_active'],
            'updated_by' => $uid,
            'updated_date' => $now,
        ];

        if ($this->isEditing && $this->editingId) {
            $item = EeclItem::findOrFail($this->editingId);
            $item->update($data);
            $this->dispatch('toast', type: 'success', message: 'EECL item updated successfully.');
        } else {
            EeclItem::create(array_merge($data, [
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
            ]));
            $this->dispatch('toast', type: 'success', message: 'EECL item added successfully.');
        }

        $this->closeModal();
    }

    public function toggleActive(int $id): void
    {
        $item = EeclItem::findOrFail($id);
        $item->update([
            'is_active' => !$item->is_active,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $statusText = $item->is_active ? 'activated' : 'deactivated';
        $this->dispatch('toast', type: 'info', message: "EECL item {$statusText}.");
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->editingId) {
            $item = EeclItem::findOrFail($this->editingId);
            $item->update([
                'history' => 0,
                'is_active' => false,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);

            $this->dispatch('toast', type: 'success', message: 'EECL item archived.');
        }
        $this->showDeleteModal = false;
        $this->reset(['editingId']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'item_code',
            'subcategory',
            'item_name',
            'description',
            'is_active',
        ]);
        $this->category = 'Diagnostic Services';
        $this->unit = 'service';
        $this->fixed_fee = '0.00';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $itemsQuery = EeclItem::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('item_name', 'like', '%' . $this->search . '%')
                        ->orWhere('item_code', 'like', '%' . $this->search . '%')
                        ->orWhere('subcategory', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedCategory, fn ($q) => $q->where('category', $this->selectedCategory))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy($this->sortField, $this->sortDirection);

        $items = $itemsQuery->paginate($this->perPage);

        // Stats calculation
        $stats = [
            'total' => EeclItem::count(),
            'active' => EeclItem::where('is_active', true)->count(),
            'diagnostic' => EeclItem::where('category', 'Diagnostic Services')->count(),
            'er_services' => EeclItem::where('category', 'ER Services')->count(),
            'imaging' => EeclItem::where('category', 'Imaging Services')->count(),
            'medicines' => EeclItem::where('category', 'Medicines')->count(),
            'supplies' => EeclItem::where('category', 'Supplies')->count(),
        ];

        return view('livewire.philhealth.eecl-item-crud', [
            'items' => $items,
            'stats' => $stats,
            'categories' => self::CATEGORIES,
        ])->layout('components.layouts.app', ['title' => 'EECL Master Catalog — PhilHealth OECB']);
    }
}
