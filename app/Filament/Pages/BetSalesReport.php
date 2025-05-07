<?php
namespace App\Filament\Pages;

use App\Models\Bet;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class BetSalesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Bet Sales';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.bet-sales-report';

    public $selectedDate;
    public $selectedTellerId = 'all';
    public $dateOptions = [];
    public $tellerOptions = [];
    
    #[Url]
    public $search = '';
    
    public $perPage = 15;
    public $page = 1;

    public function mount(): void
    {
        // Get all unique bet dates
        $this->dateOptions = Bet::selectRaw('DATE(bet_date) as date')
            ->distinct()
            ->orderByDesc('date')
            ->get()
            ->map(function($bet) {
                $date = $bet->date ?? '';
                return [
                    'date' => $date,
                    'label' => $date ? Carbon::parse($date)->format('F j, Y') : 'Unknown Date',
                ];
            })->values()->toArray();
            
        // If no bets exist yet, add today's date as an option
        if (empty($this->dateOptions)) {
            $today = Carbon::today()->format('Y-m-d');
            $this->dateOptions[] = [
                'date' => $today,
                'label' => Carbon::today()->format('F j, Y'),
            ];
        }

        // Set default to the most recent date
        $this->selectedDate = $this->dateOptions[0]['date'] ?? Carbon::today()->format('Y-m-d');
        
        // Debug information
        logger('Available dates: ' . json_encode($this->dateOptions));
        logger('Selected date: ' . $this->selectedDate);

        // Get all tellers
        $this->tellerOptions = User::where('role', 'teller')
            ->orderBy('name')
            ->get()
            ->map(function($teller) {
                return [
                    'id' => $teller->id,
                    'name' => $teller->name,
                ];
            })->toArray();
    }

    public function updatedSelectedDate(): void
    {
        // Reset pagination when date changes
        $this->page = 1;
    }

    public function updatedSelectedTellerId(): void
    {
        // Reset pagination when teller changes
        $this->page = 1;
    }
    
    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
    }

    #[Computed]
    public function bets()
    {
        // Debug information
        logger('Selected Date: ' . $this->selectedDate);
        
        // Get all bets for the selected date, handling both date and datetime formats
        $query = Bet::with(['draw', 'gameType', 'teller', 'location'])
            ->whereDate('bet_date', $this->selectedDate);

        if ($this->selectedTellerId !== 'all') {
            $query->where('teller_id', $this->selectedTellerId);
        }
        
        // Apply search if provided
        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                  ->orWhere('bet_number', 'like', "%{$search}%")
                  ->orWhereHas('teller', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get all results for mapping
        $allBets = $query->orderBy('created_at', 'desc')->get();
        
        // Map the results to the format we need
        $mappedBets = $allBets->map(function($bet) {
                $status = 'Active';
                if ($bet->is_rejected) {
                    $status = 'Cancelled';
                } elseif ($bet->is_claimed) {
                    $status = 'Claimed';
                } elseif ($bet->claim) {
                    $status = 'Won';
                }

                return [
                    'id' => $bet->id,
                    'ticket_id' => $bet->ticket_id,
                    'draw_date' => $bet->draw->draw_date->format('Y-m-d'),
                    'draw_time' => Carbon::createFromFormat('H:i:s', $bet->draw->draw_time)->format('g:i A'),
                    'game_type' => $bet->gameType->name ?? '',
                    'bet_number' => $bet->bet_number,
                    'amount' => $bet->amount,
                    'status' => $status,
                    'teller_name' => $bet->teller->name ?? '',
                    'location_name' => $bet->location->name ?? '',
                    'bet_date' => $bet->bet_date ? Carbon::parse($bet->bet_date)->format('F j, Y g:i A') : '',
                ];
            })->toArray();
            
        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($mappedBets);
        
        // Get items for current page
        $items = array_slice($mappedBets, ($page - 1) * $perPage, $perPage);
        
        // Create a paginator instance
        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
