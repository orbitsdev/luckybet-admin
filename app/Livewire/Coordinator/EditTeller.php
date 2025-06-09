<?php

namespace App\Livewire\Coordinator;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class EditTeller extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public User $record;

    public function mount(User $record): void
    {
        $this->record = $record;
        
        // Load the record with its relationships
        $this->record->load('commission');
        
        // Prepare data array with record attributes
        $data = $this->record->attributesToArray();
        
        // Ensure commission exists
        if (!$this->record->commission) {
            // Create default commission if it doesn't exist
            $this->record->commission()->create();
            $this->record->refresh();
        }
        
        // Fill the form with record data
        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->description('Basic user details for identification and login.')
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('name')
                                ->label('Full Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('username')
                                ->label('Username')
                                ->required()
                                ->maxLength(255),
                        ])->columns(2),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('email')
                                ->label('Email Address')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->label('Phone Number')
                                ->prefixIcon('heroicon-o-phone')
                                ->tel()
                                ->mask('99999999999')
                                ->maxLength(255),
                        ])->columns(2),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Set to inactive to disable user login.')
                            ->required(),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('Security')
                    ->description('Update password (leave blank to keep current password).')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => bcrypt($state)),
                    ]),
                Forms\Components\Section::make('Profile Photo')
                    ->description('Update profile photo for this teller.')
                    ->schema([
                        FileUpload::make('profile_photo_path')
                            ->image()
                            ->maxSize(3072)
                            ->hint('Maximum size: 3MB')
                            ->imagePreviewHeight('100'),
                    ]),
                Forms\Components\Section::make('Commission')
                    ->description('Set commission rate for this teller.')
                    ->schema([
                        Forms\Components\Group::make()
                            ->relationship('commission')
                            ->schema([
                                Forms\Components\TextInput::make('rate')
                                    ->label('Commission Rate')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->step(0.01)
                                    ->helperText('Enter as decimal (e.g. 0.15 for 15%)'),
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function update()
    {
        $data = $this->form->getState();
        
        // Remove password if empty
        if (empty($data['password'])) {
            unset($data['password']);
        }
        
        $this->record->update($data);
        
        // Save relationships (like commission)
        $this->form->model($this->record)->saveRelationships();
        
        Notification::make()
            ->title('Teller Updated')
            ->success()
            ->body('The teller has been updated successfully.')
            ->send();
            
        return redirect()->route('coordinator.tellers');
    }

    public function render(): View
    {
        // For debugging - check if commission relationship is loaded
        $hasCommission = $this->record->relationLoaded('commission');
        $commissionRate = $this->record->commission ? $this->record->commission->rate : 'No commission record';
        
        // Add debug data to the view
        return view('livewire.coordinator.edit-teller');
    }
}
