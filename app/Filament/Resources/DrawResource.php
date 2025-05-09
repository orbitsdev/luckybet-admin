<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrawResource\Pages;
use App\Filament\Resources\DrawResource\RelationManagers;
use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrawResource extends Resource
{
    protected static ?string $model = Draw::class;

    protected static ?string $navigationGroup = 'Draw Management';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Draw Information')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('draw_date')
                                        ->required()
                                        ->default(now()),
                                    Forms\Components\Select::make('draw_time')
                                        ->label('Draw Time')
                                        ->options(function() {
                                            $schedules = \App\Models\Schedule::where('is_active', true)->get();
                                            $options = [];
                                            foreach ($schedules as $schedule) {
                                                $options[$schedule->draw_time] = "{$schedule->name} ({$schedule->draw_time})";
                                            }
                                            return $options;
                                        })
                                        ->searchable()
                                        ->required(),
                                ]),
                            Forms\Components\Toggle::make('is_open')
                                ->required()->default(true)
                                ->live()
                                ->helperText('Note: Winning numbers can only be entered after the draw is closed.'),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->helperText('Hide this draw from dropdowns and betting screens without deleting.'),
                        ]),
                    Forms\Components\Wizard\Step::make('Winning Numbers')
                        ->icon('heroicon-o-trophy')
                        ->schema([
                            Forms\Components\Section::make('Add Winning Numbers')
                                ->description('Enter the winning numbers for this draw')
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->relationship('result')
                                        ->schema([
                                            // Winning number fields
                                            Forms\Components\TextInput::make('s2_winning_number')
                                                ->label('2-Digit (S2)')
                                                ->mask('99')
                                                ->nullable(),
                                            Forms\Components\TextInput::make('s3_winning_number')
                                                ->label('3-Digit (S3)')
                                                ->mask('999')
                                                ->nullable(),
                                            Forms\Components\TextInput::make('d4_winning_number')
                                                ->label('4-Digit (D4)')
                                                ->mask('9999')
                                                ->nullable(),
                                        ])
                                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $livewire) {
                                            // Get the current Draw record being edited
                                            $draw = $livewire->record;

                                            // Add draw_date and draw_time to the Result data
                                            $data['draw_date'] = $draw->draw_date;
                                            $data['draw_time'] = $draw->draw_time;

                                            return $data;
                                        })
                                        ->columns(3)
                                ])
                        ])->hidden(function (string $operation, $livewire, $get): bool {
                            // Hide when creating a new record
                            if ($operation === 'create') {
                                return true;
                            }
                            
                            // Hide when the draw is still open - use the live toggle value
                            if ($operation === 'edit') {
                                return $get('is_open') === true;
                            }
                            
                            return false;
                        }),
                ])
                ->skippable()->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time('h:i A')
                    ->label('Draw Time')
                    ->sortable(),
                // Game type column removed as per documentation
                Tables\Columns\IconColumn::make('is_open')
                    ->boolean()
                    ->tooltip('Whether this draw is currently open for accepting bets.'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->tooltip('Hide this draw from dropdowns and betting screens without deleting.'),
                // Custom columns for winning numbers from result relationship
                Tables\Columns\TextColumn::make('result.s2_winning_number')
                    ->label('S2 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('result.s3_winning_number')
                    ->label('S3 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('result.d4_winning_number')
                    ->label('D4 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn (Builder $query): Builder => $query->orderBy('draw_date', 'desc')->orderBy('draw_time', 'desc'));
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ResultRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDraws::route('/'),
            'create' => Pages\CreateDraw::route('/create'),
            'edit' => Pages\EditDraw::route('/{record}/edit'),
        ];
    }
}
