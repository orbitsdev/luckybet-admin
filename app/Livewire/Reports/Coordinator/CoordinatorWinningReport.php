<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\GameType;
use App\Models\User;
use Carbon\Carbon;
// Removed WithPagination to avoid trait conflict with InteractsWithTable
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Contracts\View\View as ViewContract;

class CoordinatorWinningReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $date;
    public $game_type_id;
    public $location_id;
    public $teller_id;

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->game_type_id = null;
        $this->location_id = null;
        $this->teller_id = null;
    }

    public function render(): ViewContract
    {
        return view('livewire.coordinator.reports.winning');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Date')
                    ->default(Carbon::today())
                    ->required(),
                Select::make('game_type_id')
                    ->label('Game Type')
                    ->options(GameType::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Select::make('teller_id')
                    ->label('Teller')
                    ->options(function () {
                        // Only show tellers assigned to the coordinator's locations
                        return User::where('role', 'teller')
                            ->whereHas('coordinator', function ($query) {
                                $query->where('id', Auth::id());
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Query to get winning bets for tellers under this coordinator
                Bet::query()
                    ->join('users', 'bets.teller_id', '=', 'users.id')
                    ->join('draws', 'bets.draw_id', '=', 'draws.id')
                    ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
                    ->whereHas('teller.coordinator', function ($query) {
                        $query->where('id', Auth::id());
                    })
                    ->where('is_winner', true)
                    ->when($this->date, function ($query, $date) {
                        return $query->whereDate('bets.created_at', $date);
                    })
                    ->when($this->game_type_id, function ($query, $game_type_id) {
                        return $query->where('bets.game_type_id', $game_type_id);
                    })
                    ->when($this->teller_id, function ($query, $teller_id) {
                        return $query->where('bets.teller_id', $teller_id);
                    })
                    ->select(
                        'bets.*',
                        'bets.bet_number',
                        'bets.amount as bet_amount',
                        'bets.ticket_id',
                        'users.name as teller_name',
                        'draws.draw_time',
                        'game_types.name as game_type_name'
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bet Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller_name')
                    ->label('Teller')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label('Ticket ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('bet_amount')
                    ->label('Bet Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Winning Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('game_type_name')
                    ->label('Game Type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->label('Draw Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Game Type')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('teller_id')
                    ->relationship('teller', 'name', function ($query) {
                        // Only show tellers assigned to the coordinator
                        return $query->whereHas('coordinator', function ($q) {
                            $q->where('id', Auth::id());
                        });
                    })
                    ->label('Teller')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }

    public function refreshTable()
    {
        $this->resetTable();
    }
}
