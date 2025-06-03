<?php

namespace App\Livewire\Coordinator;

use App\Models\LowWinNumber;
use App\Models\GameType;
use App\Models\Location;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class ListLowWinNumbers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Statistics for low win numbers
     */
    public $total_low_win_numbers = 0;
    public $total_active_low_win_numbers = 0;
    public $total_inactive_low_win_numbers = 0;

    public function render(): View
    {
        return view('livewire.coordinator.list-low-win-numbers');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Only show low win numbers for locations assigned to the coordinator
                LowWinNumber::query()
                    ->whereHas('location', function (Builder $query) {
                        $query->whereIn('id', Auth::user()->locations->pluck('id'));
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('game_type.name')
                    ->label('Game Type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('Number')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->relationship('location', 'name', function (Builder $query) {
                        // Only show locations assigned to the coordinator
                        return $query->whereIn('id', Auth::user()->locations->pluck('id'));
                    })
                    ->label('Location')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('game_type')
                    ->relationship('game_type', 'name')
                    ->label('Game Type')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Statuses')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->groups([
                Group::make('location.name')
                    ->label('Location'),
                Group::make('game_type.name')
                    ->label('Game Type'),
                Group::make('is_active')
                    ->label('Status')
                    ->getTitleFromRecordUsing(fn (Model $record): string => $record->is_active ? 'Active' : 'Inactive'),
            ])
            ->poll('60s')
            ->defaultGroup('location.name')
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }

    #[On('low-win-number-created')]
    #[On('low-win-number-updated')]
    #[On('low-win-number-deleted')]
    public function refresh(): void
    {
        $this->refreshTable();
    }
}
