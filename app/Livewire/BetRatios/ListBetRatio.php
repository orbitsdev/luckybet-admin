<?php

namespace App\Livewire\BetRatios;

use App\Models\Draw;
use Filament\Tables;
use Livewire\Component;
use App\Models\BetRatio;
use App\Models\GameType;
use App\Models\Location;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ListBetRatio extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $filterDate = null;
    public array $betRatioStats = [];


    public function __construct()
{
    $this->listeners = [
        'filament.table.filter' => 'handleFilterChange',
        'filament.table.filters.reset' => 'resetTableFilters',
    ];
}

    public function mount(): void
    {
        $this->filterDate = now()->toDateString();
        $this->computeStats();
    }

    public function computeStats(): void
{
    $filter = $this->tableFilters['draw_date']['value'] ?? now()->toDateString();
    $this->filterDate = \Carbon\Carbon::parse($filter)->toDateString();
    
    $query = BetRatio::query()
        ->whereHas('draw', fn ($q) => $q->whereDate('draw_date', $this->filterDate));

    $totalRatios = $query->count();
    $totalMaxAmount = $query->sum('max_amount');

    $this->betRatioStats['total_ratios'] = $totalRatios;
    $this->betRatioStats['total_max_amount'] = $totalMaxAmount;
    $this->betRatioStats['avg_max_amount'] = $totalRatios > 0 ? $totalMaxAmount / $totalRatios : 0;

    $this->betRatioStats['game_type_stats'] = BetRatio::whereHas('draw', fn ($q) =>
        $q->whereDate('draw_date', $this->filterDate)
    )
        ->join('game_types', 'bet_ratios.game_type_id', '=', 'game_types.id')
        ->select('game_types.name')
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('SUM(bet_ratios.max_amount) as total_max_amount')
        ->groupBy('game_types.name')
        ->get()
        ->keyBy('name')
        ->toArray();

    $this->betRatioStats['location_stats'] = BetRatio::whereHas('draw', fn ($q) =>
        $q->whereDate('draw_date', $this->filterDate)
    )
        ->join('locations', 'bet_ratios.location_id', '=', 'locations.id')
        ->select('locations.name')
        ->selectRaw('COUNT(*) as total')
        ->selectRaw('SUM(bet_ratios.max_amount) as total_max_amount')
        ->groupBy('locations.name')
        ->get()
        ->keyBy('name')
        ->toArray();
}
public function handleFilterChange(): void
{
    $this->computeStats();
    $this->dispatch('refresh');
}


    public function updatedFilterDate(): void
    {
        $this->computeStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BetRatio::query()
                    ->whereHas('draw', function ($q) {
                        $q->whereDate('draw_date', $this->filterDate);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')->label('Draw Date')->date()->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')->label('Draw Time')->time('h:i A')->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')->label('Game Type')->sortable(),
                Tables\Columns\TextColumn::make('bet_number')->searchable(),
                Tables\Columns\TextColumn::make('sub_selection')->sortable(),
                Tables\Columns\TextColumn::make('max_amount')->money('PHP')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable(),
                Tables\Columns\TextColumn::make('location.name')->label('Location')->sortable(),
            ])
           ->filters([
    Tables\Filters\Filter::make('draw_date')
        ->label('Draw Date')
        ->form([
            \Filament\Forms\Components\DatePicker::make('draw_date')
                ->label('Draw Date')
                ->default(fn () => $this->filterDate)
                ->live()
                ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                    $livewire->filterDate = \Carbon\Carbon::parse($state)->toDateString();
                    $livewire->computeStats(); // manually trigger recompute
                    $livewire->dispatch('refresh'); // optionally refresh table
                }),
        ])
        ->indicateUsing(function (array $data): ?string {
            if (!$data['draw_date']) {
                return null;
            }
            return 'Date: ' . \Carbon\Carbon::parse($data['draw_date'])->format('F j, Y');
        })
        ->query(fn ($query, $data) => $query->when(
            $data['draw_date'] ?? null,
            fn ($q, $date) => $q->whereHas('draw', fn ($q) => $q->whereDate('draw_date', $date))
        )),
],

        layout: FiltersLayout::AboveContent
        );
    }

    public function resetTableFilters(): void
{
    parent::resetTableFilters();
    $this->filterDate = now()->toDateString();
    $this->computeStats();
}


    public function render(): View
    {
        return view('livewire.bet-ratios.list-bet-ratio');
    }
}
