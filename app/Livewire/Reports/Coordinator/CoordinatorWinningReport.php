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
        // Get winning bets for tellers under this coordinator using the same approach as WinningReport
        $query = Bet::query()
            ->with(['draw.result', 'gameType', 'teller'])
            ->whereHas('teller.coordinator', function ($query) {
                $query->where('id', Auth::id());
            })
            ->whereHas('draw.result')
            ->when($this->date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->when($this->game_type_id, function ($query, $game_type_id) {
                return $query->where('game_type_id', $game_type_id);
            })
            ->when($this->teller_id, function ($query, $teller_id) {
                return $query->where('teller_id', $teller_id);
            })
            // Identify winning bets by checking against results
            ->where(function ($query) {
                // For S2 game type
                $query->where(function ($q) {
                    $q->where('game_type_id', 1)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s2_winning_number)');
                })
                // For S3 game type
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 2)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s3_winning_number)');
                })
                // For D4 game type - exact match
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.d4_winning_number)');
                })
                // For D4-S2 sub-selection - we need to compare the last 2 digits of D4 winning number
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->where('d4_sub_selection', 'S2')
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 2) = LPAD(bets.bet_number, 2, "0"))');
                })
                // For D4-S3 sub-selection - we need to compare the last 3 digits of D4 winning number
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->where('d4_sub_selection', 'S3')
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 3) = LPAD(bets.bet_number, 3, "0"))');
                });
            });

        // Calculate summary statistics
        // First, get all bets for this coordinator's tellers
        $allBetsQuery = Bet::query()
            ->whereHas('teller.coordinator', function ($query) {
                $query->where('id', Auth::id());
            })
            ->when($this->date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->when($this->game_type_id, function ($query, $game_type_id) {
                return $query->where('game_type_id', $game_type_id);
            })
            ->when($this->teller_id, function ($query, $teller_id) {
                return $query->where('teller_id', $teller_id);
            });
            
        $totalBets = $allBetsQuery->count();
        $totalSales = $allBetsQuery->sum('amount');
        
        // Clone the winning bets query for counts and sums
        $winningBetsQuery = clone $query;
        $winningBets = $winningBetsQuery->count();
        $totalPayouts = $winningBetsQuery->sum('winning_amount');

        // Get winning numbers with pagination
        $winningNumbers = Bet::query()
            ->with(['draw', 'teller'])
            ->whereHas('teller.coordinator', function ($query) {
                $query->where('id', Auth::id());
            })
            ->whereHas('draw.result')
            ->when($this->date, function ($query, $date) {
                return $query->whereDate('created_at', $date);
            })
            ->when($this->game_type_id, function ($query, $game_type_id) {
                return $query->where('game_type_id', $game_type_id);
            })
            ->when($this->teller_id, function ($query, $teller_id) {
                return $query->where('teller_id', $teller_id);
            })
            // Use the same winning condition as above
            ->where(function ($query) {
                // For S2 game type
                $query->where(function ($q) {
                    $q->where('game_type_id', 1)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s2_winning_number)');
                })
                // For S3 game type
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 2)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s3_winning_number)');
                })
                // For D4 game type - exact match
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.d4_winning_number)');
                })
                // For D4-S2 sub-selection
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->where('d4_sub_selection', 'S2')
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 2) = LPAD(bets.bet_number, 2, "0"))');
                })
                // For D4-S3 sub-selection
                ->orWhere(function ($q) {
                    $q->where('game_type_id', 3)
                      ->where('d4_sub_selection', 'S3')
                      ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 3) = LPAD(bets.bet_number, 3, "0"))');
                });
            })
            ->select('bet_number', 'draw_id', 
                    DB::raw('COUNT(*) as total_bets'), 
                    DB::raw('SUM(amount) as total_sales'), 
                    DB::raw('COUNT(*) as winning_bets'), 
                    DB::raw('SUM(winning_amount) as total_payouts'), 
                    'created_at')
            ->groupBy('bet_number', 'draw_id', 'created_at')
            ->paginate(10);

        // Get winning bets list with pagination
        $winningBetsList = $query->paginate(10);

        return view('livewire.coordinator.reports.winning', [
            'totalBets' => $totalBets,
            'totalSales' => $totalSales,
            'winningBets' => $winningBets,
            'totalPayouts' => $totalPayouts,
            'winningNumbers' => $winningNumbers,
            'winningBetsList' => $winningBetsList
        ]);
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
                    ->whereHas('draw.result')
                    ->when($this->date, function ($query, $date) {
                        return $query->whereDate('bets.created_at', $date);
                    })
                    ->when($this->game_type_id, function ($query, $game_type_id) {
                        return $query->where('bets.game_type_id', $game_type_id);
                    })
                    ->when($this->teller_id, function ($query, $teller_id) {
                        return $query->where('bets.teller_id', $teller_id);
                    })
                    // Identify winning bets by checking against results
                    ->where(function ($query) {
                        // For S2 game type
                        $query->where(function ($q) {
                            $q->where('bets.game_type_id', 1)
                              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s2_winning_number)');
                        })
                        // For S3 game type
                        ->orWhere(function ($q) {
                            $q->where('bets.game_type_id', 2)
                              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s3_winning_number)');
                        })
                        // For D4 game type - exact match
                        ->orWhere(function ($q) {
                            $q->where('bets.game_type_id', 3)
                              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.d4_winning_number)');
                        })
                        // For D4-S2 sub-selection
                        ->orWhere(function ($q) {
                            $q->where('bets.game_type_id', 3)
                              ->where('bets.d4_sub_selection', 'S2')
                              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                        RIGHT(results.d4_winning_number, 2) = LPAD(bets.bet_number, 2, "0"))');
                        })
                        // For D4-S3 sub-selection
                        ->orWhere(function ($q) {
                            $q->where('bets.game_type_id', 3)
                              ->where('bets.d4_sub_selection', 'S3')
                              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                        RIGHT(results.d4_winning_number, 3) = LPAD(bets.bet_number, 3, "0"))');
                        });
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
    
    public function resetFilters()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->game_type_id = null;
        $this->teller_id = null;
        $this->resetTable();
    }
    
    public function applyFilters()
    {
        $this->resetTable();
    }
}
