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
    public $totalWinAmount = 0;
    public $totalWinners = 0;

    use InteractsWithActions;
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Winners';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.winners-report';

    public $selectedDate;

    #[Url]
    public $search = '';

    public $perPage = 10;
    public $page = 1;

    public function mount(): void
    {
        // Set default to today's date
        $this->selectedDate = Carbon::today()->format('Y-m-d');
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
        $date = $this->selectedDate ?? Carbon::today()->format('Y-m-d');
        $results = Result::where('draw_date', $date)->get();
        $winners = [];

        foreach ($results as $result) {
            // Defensive: skip if result or draw_id is missing
            if (!$result || !$result->draw_id) continue;

            $bets = Bet::with(['gameType', 'teller', 'draw'])
                ->where('draw_id', $result->draw_id)
                ->where('is_rejected', false)
                ->get();

            foreach ($bets as $bet) {
                // Defensive: skip if bet is null
                if (!$bet) continue;

                // Use the model's isHit() method for robust winner logic
                try {
                    if (method_exists($bet, 'isHit') && $bet->isHit()) {
                        $claimStatus = $bet->is_claimed ? 'Claimed' : 'Pending';
                        $claimedAt = $bet->claim_at ? Carbon::parse($bet->claim_at)->format('Y-m-d g:i A') : '-';
                        $drawDate = $result->draw_date instanceof Carbon ? $result->draw_date->format('Y-m-d') : (is_string($result->draw_date) ? $result->draw_date : '');
                        $drawTime = $result->draw_time ? (Carbon::hasFormat($result->draw_time, 'H:i:s') ? Carbon::createFromFormat('H:i:s', $result->draw_time)->format('g:i A') : $result->draw_time) : '';
                        $gameTypeName = $bet->gameType->name ?? '';
                        $winningNumber = $bet->gameType->code === 'S2' ? $result->s2_winning_number : ($bet->gameType->code === 'S3' ? $result->s3_winning_number : ($bet->gameType->code === 'D4' ? $result->d4_winning_number : ''));
                        $winAmount = $claim && isset($claim->amount) ? $claim->amount : ($bet->winning_amount ?? 0);
                        $tellerName = $bet->teller->name ?? '';
                        $winners[] = [
                            'id' => $bet->id,
                            'ticket_id' => $bet->ticket_id ?? '',
                            'draw_date' => $drawDate,
                            'draw_time' => $drawTime,
                            'game_type' => $gameTypeName,
                            'winning_number' => $winningNumber,
                            'bet_number' => $bet->bet_number ?? '',
                            'win_amount' => $winAmount,
                            'is_low_win' => $bet->is_low_win ?? false,
                            'claim_status' => $claimStatus,
                            'claimed_at' => $claimedAt,
                            'teller_name' => $tellerName,
                            'd4_sub_selection' => $bet->d4_sub_selection ?? null,
                        ];
                    }
                } catch (\Throwable $e) {
                    // Log error or continue gracefully
                    continue;
                }
            }
        }

        // Sort winners by draw time and date descending
        usort($winners, function($a, $b) {
            return strcmp(($b['draw_date'] ?? '') . ($b['draw_time'] ?? ''), ($a['draw_date'] ?? '') . ($a['draw_time'] ?? ''));
        });

        // Apply search filter if search term is provided
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $winners = array_filter($winners, function($winner) use ($search) {
                return (isset($winner['ticket_id']) && str_contains(strtolower($winner['ticket_id']), $search)) ||
                       (isset($winner['bet_number']) && str_contains(strtolower($winner['bet_number']), $search)) ||
                       (isset($winner['winning_number']) && str_contains(strtolower($winner['winning_number']), $search));
            });
        }

        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($winners);
        $items = array_slice($winners, ($page - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $this->totalWinAmount = array_sum(array_column($winners, 'win_amount'));
        $this->totalWinners = count($winners);
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
