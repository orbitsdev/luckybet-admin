<?php

namespace App\Livewire;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\BetRatio;
use App\Models\Location;
use App\Models\LowWinNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class AdminDashboard extends Component
{
    public $todayStats = [];
    public $weekStats = [];
    public $drawStats = [];
    public $locationStats = [];
    public $userStats = [];
    public $selectedDate;

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }
    
    public function loadStats()
    {
        $this->loadTodayStats();
        $this->loadWeekStats();
        $this->loadDrawStats();
        $this->loadLocationStats();
        $this->loadUserStats();
    }

    protected function loadTodayStats()
    {
        $today = Carbon::parse($this->selectedDate);

        $this->todayStats = [
            'totalBets' => Bet::whereDate('bet_date', $today)->count(),
            'totalAmount' => Bet::whereDate('bet_date', $today)->sum('amount'),
            'totalWinningAmount' => Bet::whereDate('bet_date', $today)
                ->where('is_claimed', true)
                ->sum('winning_amount'),
            'soldOutNumbers' => BetRatio::where('max_amount', 0)
                ->whereHas('draw', function ($query) use ($today) {
                    $query->whereDate('draw_date', $today);
                })
                ->count(),
            'lowWinNumbers' => LowWinNumber::whereHas('draw', function ($query) use ($today) {
                $query->whereDate('draw_date', $today);
            })->count(),
            'upcomingDraws' => Draw::whereDate('draw_date', $today)
                ->where('is_open', true)
                ->count(),
            'completedDraws' => Draw::whereDate('draw_date', $today)
                ->where('is_open', false)
                ->whereHas('result')
                ->count(),
        ];
    }

    protected function loadWeekStats()
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        $endOfWeek = $selectedDate->copy()->endOfWeek();

        $this->weekStats = [
            'totalBets' => Bet::whereBetween('bet_date', [$startOfWeek, $endOfWeek])->count(),
            'totalAmount' => Bet::whereBetween('bet_date', [$startOfWeek, $endOfWeek])->sum('amount'),
            'totalWinningAmount' => Bet::whereBetween('bet_date', [$startOfWeek, $endOfWeek])
                ->where('is_claimed', true)
                ->sum('winning_amount'),
            'dailyBets' => Bet::select(
                    DB::raw('DATE(bet_date) as date'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total_amount')
                )
                ->whereBetween('bet_date', [$startOfWeek, $endOfWeek])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date')
                ->toArray(),
        ];
    }

    protected function loadDrawStats()
    {
        $today = Carbon::parse($this->selectedDate);
        $tomorrow = $today->copy()->addDay();

        // Get completed draw IDs using a separate query
        $completedDrawIds = DB::table('results')
            ->join('draws', 'results.draw_id', '=', 'draws.id')
            ->whereDate('draws.draw_date', $today)
            ->pluck('draws.id')
            ->toArray();

        // Get today's draws without trying to eager load the result relationship
        $todayDraws = Draw::whereDate('draw_date', $today)
            ->orderBy('draw_time')
            ->with(['betRatios' => function ($query) {
                $query->where('max_amount', 0);
            }])
            ->get();
        
        // Add a completed flag to each draw based on the IDs we collected
        foreach ($todayDraws as $draw) {
            $draw->is_completed = in_array($draw->id, $completedDrawIds);
        }

        // Get tomorrow's draws
        $tomorrowDraws = Draw::whereDate('draw_date', $tomorrow)
            ->orderBy('draw_time')
            ->with(['betRatios' => function ($query) {
                $query->where('max_amount', 0);
            }])
            ->get();

        // Get game type distribution
        $gameTypeDistribution = Bet::join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->select('game_types.name', DB::raw('COUNT(*) as count'))
            ->whereDate('bet_date', $today)
            ->groupBy('game_types.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        $this->drawStats = [
            'todayDraws' => $todayDraws,
            'tomorrowDraws' => $tomorrowDraws,
            'gameTypeDistribution' => $gameTypeDistribution,
        ];
    }

    protected function loadLocationStats()
    {
        $today = Carbon::parse($this->selectedDate);

        // Get top 5 locations by bet amount
        $topLocations = Location::select('locations.id', 'locations.name')
            ->addSelect(DB::raw('COUNT(bets.id) as bet_count'))
            ->addSelect(DB::raw('SUM(bets.amount) as total_amount'))
            ->leftJoin('bets', 'locations.id', '=', 'bets.location_id')
            ->whereDate('bets.bet_date', $today)
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $this->locationStats = [
            'topLocations' => $topLocations,
        ];
    }

    protected function loadUserStats()
    {
        $today = Carbon::parse($this->selectedDate);

        // Get top 5 tellers by bet amount using DB facade to avoid Eloquent model issues
        $topTellers = DB::table('users')
            ->join('bets', 'users.id', '=', 'bets.teller_id')
            ->leftJoin('locations', 'bets.location_id', '=', 'locations.id')
            ->select(
                'users.id',
                'users.name',
                'locations.name as location_name',
                DB::raw('COUNT(bets.id) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount')
            )
            ->whereDate('bets.bet_date', $today)
            ->where('users.role', 'teller')
            ->groupBy('users.id', 'users.name', 'locations.name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Get user counts by role using DB facade
        $userCounts = DB::table('users')
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get()
            ->keyBy('role')
            ->toArray();
            
        // Get recent winners (bets that match winning numbers)
        // We need to use a more complex approach to handle D4 sub-selections correctly
        // First, get all potential winning bets with their results
        $potentialWinners = DB::table('bets')
            ->leftJoin('users', 'bets.customer_id', '=', 'users.id') // Make this a left join to include bets without customers
            ->join('draws', 'bets.draw_id', '=', 'draws.id')
            ->join('results', 'draws.id', '=', 'results.draw_id')
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->leftJoin('locations', 'bets.location_id', '=', 'locations.id')
            ->select(
                'bets.id',
                'users.id as user_id',
                'users.name',
                'bets.bet_number',
                'bets.amount',
                'bets.winning_amount',
                'bets.is_claimed',
                'bets.teller_id', // Add teller_id for fallback name
                'game_types.id as game_type_id',
                'game_types.name as game_type',
                'game_types.code as game_type_code',
                'bets.d4_sub_selection',
                'locations.name as location_name',
                'results.s2_winning_number',
                'results.s3_winning_number',
                'results.d4_winning_number'
            )
            ->whereDate('bets.bet_date', $today)
            ->where('bets.is_rejected', false)
            ->whereNotNull('results.id')
            ->get();
            
        // Now filter the winners using PHP to handle the D4 sub-selections properly
        $winners = collect();
        foreach ($potentialWinners as $bet) {
            $isWinner = false;
            
            // Check based on game type
            switch ($bet->game_type_id) {
                case 1: // S2
                    $isWinner = $bet->bet_number === $bet->s2_winning_number;
                    break;
                    
                case 2: // S3
                    $isWinner = $bet->bet_number === $bet->s3_winning_number;
                    break;
                    
                case 3: // D4
                    // First check exact D4 match
                    if ($bet->bet_number === $bet->d4_winning_number) {
                        $isWinner = true;
                        break;
                    }
                    
                    // Then check D4 sub-selections if applicable
                    if ($bet->d4_sub_selection && $bet->d4_winning_number) {
                        $sub = strtoupper($bet->d4_sub_selection);
                        if ($sub === 'S2') {
                            // Check if the last 2 digits of D4 winning number match the bet number
                            $lastTwoDigits = substr($bet->d4_winning_number, -2);
                            $paddedBetNumber = str_pad($bet->bet_number, 2, '0', STR_PAD_LEFT);
                            $isWinner = $lastTwoDigits === $paddedBetNumber;
                        } elseif ($sub === 'S3') {
                            // Check if the last 3 digits of D4 winning number match the bet number
                            $lastThreeDigits = substr($bet->d4_winning_number, -3);
                            $paddedBetNumber = str_pad($bet->bet_number, 3, '0', STR_PAD_LEFT);
                            $isWinner = $lastThreeDigits === $paddedBetNumber;
                        }
                    }
                    break;
            }
            
            if ($isWinner) {
                // If winning_amount is null or 0, calculate a default winning amount based on the bet amount
                // This ensures we have something to display even if winning amounts aren't set
                if (empty($bet->winning_amount)) {
                    // Use a default multiplier based on game type
                    $multiplier = 0;
                    switch ($bet->game_type_id) {
                        case 1: // S2
                            $multiplier = 70; // Example multiplier for S2
                            break;
                        case 2: // S3
                            $multiplier = 500; // Example multiplier for S3
                            break;
                        case 3: // D4
                            $multiplier = 3500; // Example multiplier for D4
                            if ($bet->d4_sub_selection) {
                                $multiplier = ($bet->d4_sub_selection === 'S2') ? 70 : 500;
                            }
                            break;
                    }
                    $bet->winning_amount = $bet->amount * $multiplier;
                }
                
                // If name is null (no customer), use a placeholder
                if (empty($bet->name)) {
                    // Try to get teller name as a fallback
                    $tellerName = DB::table('users')->where('id', $bet->teller_id)->value('name');
                    $bet->name = $tellerName ? "(Teller: {$tellerName})" : ' Customer';
                }
                
                $winners->push($bet);
            }
        }
        
        // Sort by winning amount and limit to top 5
        $recentWinners = $winners->sortByDesc('winning_amount')->take(5)->values();

        $this->userStats = [
            'topTellers' => $topTellers,
            'userCounts' => $userCounts,
            'recentWinners' => $recentWinners,
        ];
    }

    // Method to change the selected date
    public function setDate($date)
    {
        $this->selectedDate = $date;
        $this->loadStats();
    }
    
    // Method to set to today's date
    public function setToday()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }
    
    // Method to set to yesterday's date
    public function setYesterday()
    {
        $this->selectedDate = Carbon::yesterday()->format('Y-m-d');
        $this->loadStats();
    }
    
    // Date picker is now directly used in the view
    
    public function render()
    {
        return view('livewire.admin-dashboard');
    }
}
