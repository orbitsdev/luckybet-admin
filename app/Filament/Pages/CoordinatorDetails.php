<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Result;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;

class CoordinatorDetails extends Page
{
    use InteractsWithActions;
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Coordinator Details';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.coordinator-details';
    
    // Hide from navigation but make accessible via URL
    protected static bool $shouldRegisterNavigation = false;

    #[Url]
    public $coordinatorId;
    
    #[Url]
    public $date;
    
    #[Url]
    public $search = '';
    
    public $perPage = 10;
    public $page = 1;

    public function mount()
    {
        // If no coordinator ID is provided, redirect to the coordinator report
        if (!$this->coordinatorId) {
            $this->redirect('/admin/coordinator-report');
            return;
        }
        
        // If no date is provided, use today's date
        if (!$this->date) {
            $this->date = Carbon::today()->format('Y-m-d');
        }
    }

    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
    }

    #[Computed]
    public function coordinator()
    {
        return User::where('id', $this->coordinatorId)
            ->where('role', 'coordinator')
            ->first();
    }

    #[Computed]
    public function tellers()
    {
        // Get all tellers for this coordinator
        $query = User::where('role', 'teller')
            ->where('coordinator_id', $this->coordinatorId);
        
        // Apply search filter if search term is provided
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $tellers = $query->get()->map(function($teller) {
            $sales = 0;
            $hits = 0;

            // Get all draws for the selected date, eager load result
            $draws = \App\Models\Draw::with('result')->whereDate('draw_date', $this->date)->get();

            foreach ($draws as $draw) {
                // Get all bets for this teller and draw
                $bets = \App\Models\Bet::where('teller_id', $teller->id)
                    ->where('draw_id', $draw->id)
                    ->where('is_rejected', false)
                    ->get();

                $sales += $bets->sum('amount');

                // Get the result for this draw
                $result = $draw->result;
                if ($result) {
                    foreach ($bets as $bet) {
                        // Check if bet number matches winning number based on game type
                        if (
                            ($bet->game_type_id == 1 && $bet->bet_number == $result->s2_winning_number) ||
                            ($bet->game_type_id == 2 && $bet->bet_number == $result->s3_winning_number) ||
                            ($bet->game_type_id == 3 && $bet->bet_number == $result->d4_winning_number)
                        ) {
                            $hits += $bet->amount;
                        }
                    }
                }
            }

            $gross = $sales - $hits;

            return [
                'id' => $teller->id,
                'name' => $teller->name,
                'email' => $teller->email,
                'total_sales' => $sales,
                'total_hits' => $hits,
                'total_gross' => $gross,
            ];
        })->toArray();
        
        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($tellers);

        // Get items for current page
        $items = array_slice($tellers, ($page - 1) * $perPage, $perPage);

        // Create a paginator instance
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }
    
    public function backAction(): Action
    {
        return Action::make('back')
            ->label('Back to Coordinator Report')
            ->icon('heroicon-o-arrow-left')
            ->url('/admin/coordinator-report');
    }
    
    public function viewTellerDetailsAction(): Action
    {
        return Action::make('viewTellerDetails')
            ->label('View Teller Details')
            ->icon('heroicon-o-eye')
            ->url(fn (array $arguments): string => '/admin/teller-sales-summary?id=' . $arguments['teller_id']);
    }
}
