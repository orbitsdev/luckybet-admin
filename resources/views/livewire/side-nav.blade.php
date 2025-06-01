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

                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg font-bold text-base text-gray-700 nav-item {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Users Dropdown -->
                    <div class="space-y-1 mt-4 mb-4">
                        <button @click.prevent="usersOpen = !usersOpen; console.log('Users dropdown toggled:', usersOpen)"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base text-gray-700 nav-item {{ request()->routeIs('manage.users*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>Users</span>
                            </div>
                            <svg :class="usersOpen ? 'transform rotate-90' : ''"
                                class="w-4 h-4 transition-transform duration-300 ease-in-out"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <div x-cloak x-show="usersOpen" class="pl-6 mt-1 space-y-1">
                            <a href="{{ route('manage.users') }}?filter=coordinator"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('manage.users') && request()->query('filter') == 'coordinator' ? 'text-gray-600' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manage.users') && request()->query('filter') == 'coordinator' ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Coordinators</span>
                            </a>
                            <a href="{{ route('manage.users') }}?filter=teller"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('manage.users') && request()->query('filter') == 'teller' ? 'text-gray-600' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manage.users') && request()->query('filter') == 'teller' ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Tellers</span>
                            </a>
                            <a href="{{ route('manage.users') }}?filter=customer"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('manage.users') && request()->query('filter') == 'customer' ? 'text-gray-600' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manage.users') && request()->query('filter') == 'customer' ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Customers</span>
                            </a>
                        </div>
                    </div>

                    <!-- Reports Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button
                            @click.prevent="reportsOpen = !reportsOpen; console.log('Reports dropdown toggled:', reportsOpen)"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base text-gray-700 nav-item {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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
                            <a href="{{ route('reports.summary') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.summary') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.summary') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Coordinator Report</span>
                            </a>
                            <a href="{{ route('reports.tellers') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.tellers') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.tellers') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Teller Report</span>
                            </a>
                            <a href="{{ route('reports.tellers-summary') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.tellers-summary') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.tellers-summary') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="font-medium">Sales Summary</span>
                            </a>
                        </div>
                    </div>

                    <!-- Game Management Section -->
                    <div
                        class="px-4 py-2 text-lg font-heading font-semibold uppercase tracking-wider text-gray-600 mt-6 mb-1">
                        Game Management</div>
                    <div class="pt-1"></div>

                    <!-- Draw Link -->
                    <a href="{{ route('manage.draws') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item {{ request()->routeIs('manage.draws*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        <span>Draws</span>
                    </a>

                    <!-- Bet Ratio Link -->
                    <a href="{{ route('manage.bet-ratios') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item {{ request()->routeIs('manage.bet-ratios*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                        <span>Bet Ratios</span>
                    </a>

                    <!-- Sold Out Link -->
                    <a href="{{ route('manage.sold-out-numbers') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item {{ request()->routeIs('manage.sold-out-numbers*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Sold Out Numbers</span>
                    </a>

                    <!-- Low Win Link -->
                    <a href="{{ route('manage.low-win-numbers') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item {{ request()->routeIs('manage.low-win-numbers*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        <span>Low Win Numbers</span>
                    </a>
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
                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-6 py-4 rounded-lg font-bold text-base text-gray-700 nav-item relative {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Users Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button @click.prevent="usersOpen = !usersOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base text-gray-700 nav-item {{ request()->routeIs('manage.users*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }} focus:outline-none focus:ring-2 focus:ring-red-200">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>Users</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 transform transition-transform duration-200"
                                :class="{ 'rotate-180': usersOpen }">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="usersOpen" x-collapse class=" rounded-lg mt-2 mb-2 p-2 space-y-2">
                            <a href="{{ route('manage.users') }}?filter=coordinator"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('manage.users') && request()->query('filter') == 'coordinator' ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600 border-l-4 border-primary-400 bg-gray-50' }}">
                                Coordinators
                            </a>
                            <a href="{{ route('manage.users') }}?filter=teller"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('manage.users') && request()->query('filter') == 'teller' ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600 border-l-4 border-primary-400 bg-gray-50' }}">
                                Tellers
                            </a>
                            <a href="{{ route('manage.users') }}?filter=customer"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('manage.users') && request()->query('filter') == 'customer' ? 'bg-gradient-to-r from-red-50 to-red-100' : 'text-gray-600 border-l-4 border-primary-400 bg-gray-50' }}">
                                Customers
                            </a>
                        </div>
                    </div>

                    <!-- Reports Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button @click.prevent="reportsOpen = !reportsOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base text-gray-700 nav-item {{ request()->routeIs('reports.*') ? 'bg-gradient-to-r from-red-50 to-red-100' : '' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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
                        <div x-show="reportsOpen" x-collapse class=" rounded-lg mt-2 mb-2 p-2 space-y-2">
                            <a href="{{ route('reports.summary') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('reports.summary') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50 bg-gray-50' }}">
                                Coordinator Report
                            </a>
                            <a href="{{ route('reports.tellers') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('reports.tellers') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50 bg-gray-50' }}">
                                Teller Report
                            </a>
                            <a href="{{ route('reports.tellers-summary') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item {{ request()->routeIs('reports.tellers-summary') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50 bg-gray-50' }}">
                                Sales Summary
                            </a>
                        </div>
                    </div>

                    <h3 class="px-5 py-3 mt-6 text-sm font-semibold text-gray-500 uppercase tracking-wider">GAME
                        MANAGEMENT</h3>

                    <!-- Draws Link -->
                    <a href="{{ route('manage.draws') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.draws*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008H16.5V15Zm0 2.25h.008v.008H16.5v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        <span>Draws</span>
                    </a>

                    <!-- Bet Ratio Link -->
                    <a href="{{ route('manage.bet-ratios') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.bet-ratios*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                        <span>Bet Ratios</span>
                    </a>

                    <!-- Sold Out Link -->
                    <a href="{{ route('manage.sold-out-numbers') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.sold-out-numbers*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Sold Out Numbers</span>
                    </a>

                    <!-- Low Win Link -->
                    <a href="{{ route('manage.low-win-numbers') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.low-win-numbers*') ? 'bg-gradient-to-r from-red-50 to-red-100' : 'hover:bg-red-50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        <span>Low Win Numbers</span>
                    </a>

                </nav>
            </aside>
        </div>
    </div>
</div>