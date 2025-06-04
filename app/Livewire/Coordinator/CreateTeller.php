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
        $data = $this->form->getState();
        $data['coordinator_id'] = Auth::user()->id;
        $data['location_id'] = Auth::user()->location_id;
        $data['role'] = 'teller';

        $record = User::create($data);

        $this->form->model($record)->saveRelationships();

        return redirect()->route('coordinator.tellers');
    }

    public function render(): View
    {
        return view('livewire.coordinator.create-teller');
    }
}