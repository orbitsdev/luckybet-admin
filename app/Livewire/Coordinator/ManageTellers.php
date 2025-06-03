<?php

namespace App\Livewire\Coordinator;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class ManageTellers extends Component
{
    use WithPagination;
    
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $location_id;
    
    // Edit mode
    public $editMode = false;
    public $tellerId;
    
    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'password' => 'sometimes|min:8|confirmed',
        'location_id' => 'required|exists:locations,id',
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }
    
    public function openEditModal($id)
    {
        $this->resetForm();
        $this->editMode = true;
        $this->tellerId = $id;
        
        $teller = User::findOrFail($id);
        $this->name = $teller->name;
        $this->email = $teller->email;
        $this->location_id = $teller->location_id;
        
        $this->showEditModal = true;
    }
    
    public function openDeleteModal($id)
    {
        $this->tellerId = $id;
        $this->showDeleteModal = true;
    }
    
    public function resetForm()
    {
        $this->editMode = false;
        $this->tellerId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->location_id = null;
        $this->resetErrorBag();
    }
    
    public function createTeller()
    {
        // Add unique email validation for creation
        $this->rules['email'] = 'required|email|max:255|unique:users,email';
        $this->rules['password'] = 'required|min:8|confirmed';
        
        $this->validate();
        
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'teller',
            'coordinator_id' => Auth::id(),
            'location_id' => $this->location_id,
        ]);
        
        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Teller created successfully.');
    }
    
    public function updateTeller()
    {
        // Modify email validation for updates
        $this->rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($this->tellerId),
        ];
        
        // Make password optional for updates
        $this->rules['password'] = 'nullable|min:8|confirmed';
        
        $this->validate();
        
        $teller = User::findOrFail($this->tellerId);
        
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'location_id' => $this->location_id,
        ];
        
        // Only update password if provided
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }
        
        $teller->update($data);
        
        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Teller updated successfully.');
    }
    
    public function deleteTeller()
    {
        $teller = User::findOrFail($this->tellerId);
        $teller->delete();
        
        $this->showDeleteModal = false;
        session()->flash('message', 'Teller deleted successfully.');
    }
    
    public function render(): View
    {
        $coordinatorId = Auth::id();
        
        $tellers = User::where('role', 'teller')
            ->where('coordinator_id', $coordinatorId)
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->with('location')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.coordinator.manage-tellers', [
            'tellers' => $tellers,
            'locations' => \App\Models\Location::orderBy('name')->get(),
        ]);
    }
}
