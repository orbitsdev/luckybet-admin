<?php

namespace App\Livewire\Receipt;

use App\Models\Receipt;
use App\Models\User;
use App\Models\Location;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;

class ListReceipts extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;
    
    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    /**
     * The currently selected filter date
     */
    public $filterDate = null;
    
    /**
     * Receipt statistics
     */
    public $receiptStats = [];
    
    /**
     * Flag to indicate if stats are being computed
     */
    public $computingStats = false;
    
    /**
     * Timestamp of last stats computation
     */
    public $lastStatsComputation = 0;

    /**
     * Register Livewire event listeners using Livewire 3 syntax
     */
    public function __construct()
    {
        // Register event listeners
        $this->listeners = [
            'filament.table.filter' => 'handleFilterChange',
            'filament.table.filters.reset' => 'handleFilterReset',
            'compute-stats' => 'computeStatsListener',
        ];
    }

    /**
     * Handle Filament table filter changes
     */
    public function handleFilterChange($data): void
    {
        if (isset($data['tableFilters']['date'])) {
            $this->filterDate = $data['tableFilters']['date'];
            $this->computeReceiptStats();
        }
    }

    /**
     * Handle Filament table filter reset
     */
    public function handleFilterReset(): void
    {
        $this->filterDate = Carbon::today()->format('Y-m-d');
        $this->computeReceiptStats();
    }

    /**
     * Compute stats listener
     */
    public function computeStatsListener(): void
    {
        $this->computeReceiptStats();
    }

    /**
     * Compute receipt statistics for the selected date
     */
    public function computeReceiptStats()
    {
        // Set computing flag to prevent multiple simultaneous computations
        $this->computingStats = true;
        $this->lastStatsComputation = microtime(true);
        
        // Use the current filter date or default to today
        $date = $this->filterDate ?: Carbon::today()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set

        // Get total receipts for the day
        $totalReceipts = Receipt::whereDate('receipt_date', $date)
            ->where('status', '!=', 'draft')
            ->count();
            
        // Get total amount for the day
        $totalAmount = Receipt::whereDate('receipt_date', $date)
            ->where('status', '!=', 'draft')
            ->sum('total_amount');
            
        // Get receipts by status
        $receiptsByStatus = Receipt::whereDate('receipt_date', $date)
            ->where('status', '!=', 'draft')
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();
            
        // Get top 5 tellers by receipt count
        $topTellers = Receipt::whereDate('receipt_date', $date)
            ->where('status', '!=', 'draft')
            ->join('users', 'receipts.teller_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('count(*) as receipt_count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('receipt_count')
            ->limit(5)
            ->get();
            
        // Get top 5 locations by receipt count
        $topLocations = Receipt::whereDate('receipt_date', $date)
            ->where('status', '!=', 'draft')
            ->join('locations', 'receipts.location_id', '=', 'locations.id')
            ->select('locations.id', 'locations.name', DB::raw('count(*) as receipt_count'), DB::raw('sum(total_amount) as total_amount'))
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('receipt_count')
            ->limit(5)
            ->get();
            
        // Store the stats
        $this->receiptStats = [
            'total_receipts' => $totalReceipts,
            'total_amount' => $totalAmount,
            'by_status' => $receiptsByStatus,
            'top_tellers' => $topTellers,
            'top_locations' => $topLocations,
        ];
        
        // Reset computing flag
        $this->computingStats = false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Receipt::query()->where('status', '!=', 'draft'))
            ->groups([
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Receipt $record): string => $record->location?->name ?? 'No Location'),
            ])
            ->defaultGroup('location.name')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receipt_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'placed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bets_count')
                    ->label('Bets')
                    ->getStateUsing(fn (Receipt $record): int => $record->bets()->count())
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->default(Carbon::today())
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Always update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state ?? Carbon::today()->format('Y-m-d');
                                $livewire->computeReceiptStats();
                            }),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }
                        
                        return 'Date: ' . Carbon::parse($data['date'])->format('M d, Y');
                    }),
                SelectFilter::make('status')
                    ->options([
                        'placed' => 'Placed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('teller')
                    ->relationship('teller', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                ],
                layout: FiltersLayout::AboveContent
                )
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Bets')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Receipt $record): string => route('manage.receipts.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function mount(): void
    {
        // Set default date to today
        $this->filterDate = Carbon::today()->format('Y-m-d');
        
        // Compute initial stats
        $this->computeReceiptStats();
    }

    public function render(): View
    {
        return view('livewire.receipt.list-receipts', [
            'receiptStats' => $this->receiptStats,
        ]);
    }
}
