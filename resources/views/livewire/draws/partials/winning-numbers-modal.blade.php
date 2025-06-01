<div class="p-4">
    @if(!$draw->result)
        <div class="text-center py-8">
            <div class="text-gray-400 mb-2">
                <x-heroicon-o-exclamation-circle class="h-12 w-12 mx-auto" />
            </div>
            <h3 class="text-lg font-medium text-gray-900">No Winning Numbers Found</h3>
            <p class="mt-1 text-sm text-gray-500">This draw doesn't have any winning numbers set yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- S2 Winning Number -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">2-Digit (S2)</h3>
                @if($draw->result->s2_winning_number)
                    <div class="flex justify-center">
                        <div class="bg-primary-100 text-primary-800 text-2xl font-bold rounded-lg p-4 w-20 h-20 flex items-center justify-center">
                            {{ $draw->result->s2_winning_number }}
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-400 py-4">
                        <p>Not set</p>
                    </div>
                @endif
            </div>
            
            <!-- S3 Winning Number -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">3-Digit (S3)</h3>
                @if($draw->result->s3_winning_number)
                    <div class="flex justify-center">
                        <div class="bg-green-100 text-green-800 text-2xl font-bold rounded-lg p-4 w-28 h-20 flex items-center justify-center">
                            {{ $draw->result->s3_winning_number }}
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-400 py-4">
                        <p>Not set</p>
                    </div>
                @endif
            </div>
            
            <!-- D4 Winning Number -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">4-Digit (D4)</h3>
                @if($draw->result->d4_winning_number)
                    <div class="flex justify-center">
                        <div class="bg-blue-100 text-blue-800 text-2xl font-bold rounded-lg p-4 w-32 h-20 flex items-center justify-center">
                            {{ $draw->result->d4_winning_number }}
                        </div>
                    </div>
                    
                    <!-- D4 Subtypes -->
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <!-- D4-S2 (First 2 digits of D4) -->
                        <div>
                            <h4 class="text-xs font-medium text-gray-500 mb-1">D4-S2</h4>
                            <div class="flex justify-center">
                                <div class="bg-purple-100 text-purple-800 text-lg font-bold rounded-lg p-2 w-16 h-12 flex items-center justify-center">
                                    {{ substr($draw->result->d4_winning_number, 0, 2) }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- D4-S3 (First 3 digits of D4) -->
                        <div>
                            <h4 class="text-xs font-medium text-gray-500 mb-1">D4-S3</h4>
                            <div class="flex justify-center">
                                <div class="bg-indigo-100 text-indigo-800 text-lg font-bold rounded-lg p-2 w-20 h-12 flex items-center justify-center">
                                    {{ substr($draw->result->d4_winning_number, 0, 3) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-gray-400 py-4">
                        <p>Not set</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Draw Result for {{ $draw->draw_date->format('F j, Y') }} at {{ $draw->draw_time }}</p>
        </div>
    @endif
</div>
