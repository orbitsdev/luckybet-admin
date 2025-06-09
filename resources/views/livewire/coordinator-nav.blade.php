<div x-data="{ tellersOpen: true, gamesOpen: true, reportsOpen: true }">
     <!-- Sidebar and Main Content -->
     <div class="flex flex-1 overflow-hidden bg-white pt-16" @click.self="sidebarOpen = false">
        <!-- Mobile backdrop overlay -->
        <div x-show="sidebarOpen && window.innerWidth < 1024" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-gray-700 bg-opacity-70 backdrop-blur-sm"></div>

        <!-- MOBILE SIDEBAR: Only visible on mobile when toggled -->
        <div class="lg:hidden fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-50"
            :class="{
                'translate-x-0': sidebarOpen && window.innerWidth < 1024,
                '-translate-x-full': !sidebarOpen || window
                    .innerWidth >= 1024
            }">
            <aside class="h-full w-72 overflow-y-auto bg-white shadow-xl">
                <nav class="px-3 border-t-4 border-red-400 py-4">
                    <!-- Sidebar Title Section -->
                    {{-- <div class="flex items-center justify-center py-4 mb-4 border-b border-gray-100">
                        <span class="text-2xl font-bold text-red-600 audiowide-regular">LUCKY<span class="text-gray-800">BET</span></span>
                    </div> --}}

                    <a href="{{ route('coordinator.dashboard') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('coordinator.dashboard') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Teller Management Dropdown -->
                    <div class="space-y-1 mt-4 mb-4">
                        <button @click="tellersOpen = !tellersOpen"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('coordinator.tellers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>Teller Management</span>
                            </div>
                            <svg :class="tellersOpen ? 'transform rotate-90' : ''"
                                class="w-4 h-4 transition-transform duration-300 ease-in-out"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <div x-cloak x-show="tellersOpen" class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('coordinator.tellers') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('coordinator.tellers') && !request()->routeIs('coordinator.tellers.create') && !request()->routeIs('coordinator.tellers.edit') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('coordinator.tellers') && !request()->routeIs('coordinator.tellers.create') && !request()->routeIs('coordinator.tellers.edit') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span>Manage Tellers</span>
                            </a>
                           
                        </div>
                    </div>

                    

                    <!-- Game Management Section -->
                    
                    <!-- Reports Section -->
                    <div class="space-y-1 mt-4 mb-4">
                        <button @click="reportsOpen = !reportsOpen"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('coordinator.reports*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                <span>Reports</span>
                            </div>
                            <svg :class="reportsOpen ? 'transform rotate-90' : ''"
                                class="w-4 h-4 transition-transform duration-300 ease-in-out"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <div x-cloak x-show="reportsOpen" class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('coordinator.reports.teller-sales-summary') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('coordinator.reports.teller-sales-summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('coordinator.reports.teller-sales-summary') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span>Teller Sales Summary</span>
                            </a>
                            <a href="{{ route('coordinator.reports.tellers') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('coordinator.reports.tellers') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('coordinator.reports.tellers') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span>Teller Sales Report</span>
                            </a>
                            <a href="{{ route('coordinator.reports.winning') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('coordinator.reports.winning') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('coordinator.reports.winning') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span>Winning Report</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>
        </div>

        <!-- DESKTOP SIDEBAR: Toggleable on desktop -->
        <div class="hidden lg:block fixed top-16 bottom-0 left-0 transition-transform duration-300 z-40"
            :class="{
                'translate-x-0': sidebarOpen && window.innerWidth >= 1024,
                '-translate-x-full': !sidebarOpen || window
                    .innerWidth < 1024
            }">
            <aside class="h-full w-72 overflow-y-auto bg-white shadow-xl ">
                <!-- Sidebar Title Section -->
                {{-- <div class="flex items-center justify-center py-4 border-b border-gray-100">
                    <span class="text-2xl font-bold text-red-600 audiowide-regular">LUCKY<span class="text-gray-800">BET</span></span>
                </div> --}}
                <nav class="px-3 border-red-400 py-4 border-t-4 ">
                    <!-- Dashboard Link -->
                    <a href="{{ route('coordinator.dashboard') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base nav-item relative {{ request()->routeIs('coordinator.dashboard') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Teller Management Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button @click="tellersOpen = !tellersOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base nav-item {{ request()->routeIs('coordinator.tellers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>Teller Management</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 transform transition-transform duration-200"
                                :class="{ 'rotate-180': tellersOpen }">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="tellersOpen" x-collapse class="rounded-lg mt-2 mb-2 p-2 space-y-2">
                            <a href="{{ route('coordinator.tellers') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('coordinator.tellers') && !request()->routeIs('coordinator.tellers.create') && !request()->routeIs('coordinator.tellers.edit') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Manage Tellers
                            </a>
                           
                        </div>
                    </div>
                    
                    <!-- Reports Section for Desktop -->
                    <div class="space-y-1 mt-4">
                        <button @click="reportsOpen = !reportsOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base nav-item {{ request()->routeIs('coordinator.reports*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                <span>Reports</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 transform transition-transform duration-200"
                                :class="{ 'rotate-180': reportsOpen }">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="reportsOpen" x-collapse class="rounded-lg mt-2 mb-2 p-2 space-y-2">
                            <a href="{{ route('coordinator.reports.teller-sales-summary') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('coordinator.reports.teller-sales-summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Teller Sales Summary
                            </a>
                            <a href="{{ route('coordinator.reports.tellers') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('coordinator.reports.tellers') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Teller Sales Report
                            </a>
                            <a href="{{ route('coordinator.reports.winning') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('coordinator.reports.winning') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Winning Report
                            </a>
                        </div>
                    </div>

                </nav>
            </aside>
        </div>
    </div>
</div>