<?php

namespace App\Livewire\Coordinator;

use Filament\Forms;
use App\Models\User;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class CreateTeller extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {

        $this->form->fill();
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
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'username')
                            ->helperText('Must be unique across all users'),
                    ])->columns(2),
                    Forms\Components\Group::make([
                      
                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email')
                            ->helperText('Must be unique across all users'),
                        Forms\Components\TextInput::make('phone')
                                ->label('Phone Number')
                                ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->mask('99999999999')
                            ->maxLength(255),
                    ])->columns(2),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Set to inactive to disable user login.')
                        ->required(),
                ])
                ->columns(1),
            Forms\Components\Section::make('Security')
                ->description('Set a secure password for the new user.')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required()
                        ->maxLength(255),
                ]),
            Forms\Components\Section::make('Role & Assignment')
                ->description('Assign a role, location, and optional coordinator.')
                ->schema([
                   
                   
                    FileUpload::make('profile_photo_path')
                        ->image()
                        ->maxSize(3072)
                        ->hint('Maximum size: 3MB')
                        ->imagePreviewHeight('100'),
                ]),
            Forms\Components\Section::make('Commission')
                ->description('Set commission rate for teller users.')
                ->visible(fn($get) => $get('role') === 'teller')
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
                                ->default(0.15)
                                ->helperText('Enter as decimal (e.g. 0.15 for 15%)'),
                        ]),
                ]),
                        ])
                        ->statePath('data')
                        ->model(User::class);
    }

    public function create()
    {
        try {
            // Validate the form data
            $data = $this->form->getState();
            $data['coordinator_id'] = Auth::user()->id;
            $data['location_id'] = Auth::user()->location_id;
            $data['role'] = 'teller';

            // Create the user record
            $record = User::create($data);

            // Save relationships from the form
            $this->form->model($record)->saveRelationships();
            
            // Ensure commission record exists with database default rate (15%)
            if (!$record->commission) {
                $record->commission()->create();
            }

            // Add success notification
            session()->flash('success', 'Teller account created successfully with 15% commission rate.');

            return redirect()->route('coordinator.tellers');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database exceptions like duplicate entries
            $errorCode = $e->errorInfo[1] ?? null;
            
            if ($errorCode == 1062) { // MySQL duplicate entry error code
                // Extract the duplicate field from the error message
                $errorMessage = $e->getMessage();
                if (str_contains($errorMessage, 'users.users_username_unique')) {
                    $this->addError('data.username', 'This username is already taken. Please choose another one.');
                } elseif (str_contains($errorMessage, 'users.users_email_unique')) {
                    $this->addError('data.email', 'This email address is already registered. Please use another one.');
                } else {
                    $this->addError('general', 'A duplicate entry was detected. Please check your input and try again.');
                }
            } else {
                // Generic database error
                $this->addError('general', 'An error occurred while creating the teller. Please try again.');
            }
            
            return null;
        } catch (\Exception $e) {
            // Handle other exceptions
            $this->addError('general', 'An unexpected error occurred: ' . $e->getMessage());
            return null;
        }
    }

    public function render(): View
    {
        return view('livewire.coordinator.create-teller');
    }
}