<?php
namespace App\Filament\Pages;

use App\Models\Bet;
use App\Models\Claim;
use App\Models\Result;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Filament\Actions\CoordinatorReportAction;
class WinnersReport extends Page
{

    use InteractsWithActions;
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Winners';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.winners-report';

    public $selectedDate;
    public $dateOptions = [];

    #[Url]
    public $search = '';

    public $perPage = 10;
    public $page = 1;

    public function mount(): void
    {
        // Get all unique result dates
        $this->dateOptions = Result::select('draw_date')
            ->distinct()
            ->orderByDesc('draw_date')
            ->get()
            ->map(function($result) {
                $date = $result->draw_date ? $result->draw_date->format('Y-m-d') : '';
                return [
                    'date' => $date,
                    'label' => $date ? Carbon::parse($date)->format('F j, Y') : 'Unknown Date',
                ];
            })->values()->toArray();

        // Set default to the most recent date
        $this->selectedDate = $this->dateOptions[0]['date'] ?? Carbon::today()->format('Y-m-d');
    }

    public function updatedSelectedDate(): void
    {
        // Reset pagination when date changes
        $this->page = 1;
    }

    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
    }

    #[Computed]
    public function winners()
    {
        // Get all results for the selected date
        $results = Result::where('draw_date', $this->selectedDate)->get();

        // Initialize winners array
        $winners = [];

        // For each result, find matching bets
        foreach ($results as $result) {
            // Get all bets for this draw
            $bets = Bet::with(['gameType', 'teller', 'draw', 'claim'])
                ->where('draw_id', $result->draw_id)
                ->where('is_rejected', false)
                ->get();

            // Check each bet to see if it's a winner
            foreach ($bets as $bet) {
                $gameType = $bet->gameType->code ?? '';
                $betNumber = $bet->bet_number;

                // Get the winning number based on game type
                $winningNumber = null;
                if ($gameType === 'S2' && !empty($result->s2_winning_number)) {
                    $winningNumber = $result->s2_winning_number;
                } elseif ($gameType === 'S3' && !empty($result->s3_winning_number)) {
                    $winningNumber = $result->s3_winning_number;
                } elseif ($gameType === 'D4' && !empty($result->d4_winning_number)) {
                    $winningNumber = $result->d4_winning_number;
                }

                // Skip if no winning number for this game type
                if (!$winningNumber) {
                    continue;
                }

                // Check if bet number matches winning number
                // For combination bets, we'd need more complex logic here
                if ($betNumber == $winningNumber) {
                    // Calculate win amount (this is simplified - you may have a more complex calculation)
                    $winAmount = $bet->amount * 70; // Example: 70x payout

                    // Get claim information if it exists
                    $claim = $bet->claim;
                    $claimStatus = $bet->is_claimed ? 'Claimed' : 'Pending';
                    $claimedAt = $claim && $claim->claim_at ? Carbon::parse($claim->claim_at)->format('Y-m-d g:i A') : '-';

                    // Add to winners array
                    $winners[] = [
                        'id' => $bet->id,
                        'ticket_id' => $bet->ticket_id,
                        'draw_date' => $result->draw_date ? $result->draw_date->format('Y-m-d') : '',
                        'draw_time' => $result->draw_time ? Carbon::createFromFormat('H:i:s', $result->draw_time)->format('g:i A') : '',
                        'game_type' => $bet->gameType->name ?? '',
                        'winning_number' => $winningNumber,
                        'bet_number' => $betNumber,
                        'win_amount' => $claim ? $claim->amount : $winAmount,
                        'claim_status' => $claimStatus,
                        'claimed_at' => $claimedAt,
                        'teller_name' => $bet->teller->name ?? '',
                    ];
                }
            }
        }

        // Sort winners by draw time
        usort($winners, function($a, $b) {
            return strcmp($b['draw_date'] . $b['draw_time'], $a['draw_date'] . $a['draw_time']);
        });

        // Apply search filter if search term is provided
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $winners = array_filter($winners, function($winner) use ($search) {
                return str_contains(strtolower($winner['ticket_id']), $search) ||
                       str_contains(strtolower($winner['bet_number']), $search) ||
                       str_contains(strtolower($winner['winning_number']), $search);
            });
        }

        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($winners);

        // Get items for current page
        $items = array_slice($winners, ($page - 1) * $perPage, $perPage);

        // Create a paginator instance
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }

    public function testAction(): Action
    {
        return Action::make('test')
            ->requiresConfirmation()
            ->action(function () {
                dd('test');
            });
    }
    
    public function coordinatorReportAction(): Action
    {
        return CoordinatorReportAction::make()
            ->arguments([
                'date' => $this->selectedDate,
            ]);
    }
    //
}
