<div>
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

                    <a href="{{ route('dashboard') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('dashboard') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
 <a href="{{ route('manage.locations') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.locations*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>Locations</span>
                    </a>
                    <!-- Users Dropdown -->
                    <div class="space-y-1 mt-4 mb-4">
                        <button @click="usersOpen = !usersOpen; console.log('Users dropdown toggled:', usersOpen)"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.users*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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

                    </div>

                    <!-- Reports Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button
                            @click="reportsOpen = !reportsOpen; console.log('Reports dropdown toggled:', reportsOpen)"
                            class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('reports.*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.summary') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="{{ request()->routeIs('reports.summary') ? 'font-medium' : '' }}">Coordinator Report</span>
                            </a>
                            <a href="{{ route('reports.tellers') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.tellers') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.tellers') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="{{ request()->routeIs('reports.tellers') ? 'font-medium' : '' }}">Teller Report</span>
                            </a>
                            <a href="{{ route('reports.tellers-summary') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.tellers-summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.tellers-summary') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="{{ request()->routeIs('reports.tellers-summary') ? 'font-medium' : '' }}">Sales Summary</span>
                            </a>
                            <a href="{{ route('reports.winning-report') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('reports.winning-report') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('reports.winning-report') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="{{ request()->routeIs('reports.winning-report') ? 'font-medium' : '' }}">Winning Report</span>
                            </a>
                            <a href="{{ route('manage.receipts') }}"
                                class="group flex items-center px-4 py-2.5 text-sm rounded-md nav-item hover:pl-5 transition-all duration-200 {{ request()->routeIs('manage.receipts*') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-l-2 border-red-500' : 'text-gray-600' }}">
                                <span
                                    class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('manage.receipts*') ? 'opacity-100 bg-red-500' : 'opacity-0 group-hover:opacity-100 bg-primary-500' }} mr-3 transition-all duration-200"></span>
                                <span class="{{ request()->routeIs('manage.receipts*') ? 'font-medium' : '' }}">Receipts</span>
                            </a>

                      </div>
                    </div>

                    <!-- Game Management Section -->
                    <div
                        class="px-4 py-2 text-lg font-heading font-semibold uppercase tracking-wider text-gray-600 mt-6 mb-1">
                        Game Management</div>
                    <div class="pt-1"></div>

                    <!-- Draw Link -->
                    <a href="{{ route('manage.draws') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.draws*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        <span>Draws</span>
                    </a>
{{--
                    <a href="{{ route('manage.winning-amounts') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.winning-amounts') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Winning Amounts</span>
                    </a> --}}

                    <!-- Bet Ratio Link -->
                    {{-- <a href="{{ route('manage.bet-ratios') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.bet-ratios*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                        <span>Bet Ratios</span>
                    </a> --}}

                    <!-- Sold Out Link -->
                    <a href="{{ route('manage.sold-out-numbers') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.sold-out-numbers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Sold Out Numbers</span>
                    </a>

                    <!-- Low Win Link -->
                    <a href="{{ route('manage.low-win-numbers') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.low-win-numbers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        <span>Low Win Numbers</span>
                    </a>

                    <!-- Bets Link -->
                    <a href="{{ route('manage.bets') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.bets') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        <span>All Bets</span>
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
                        class="group flex items-center px-6 py-4 rounded-lg text-base nav-item relative {{ request()->routeIs('dashboard') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                       <a href="{{ route('manage.locations') }}" class="group flex items-center px-5 py-3.5 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.locations*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>Locations</span>
                    </a>
                    <!-- Users Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button @click="usersOpen = !usersOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base nav-item {{ request()->routeIs('manage.users*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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
                        <div x-show="usersOpen" x-collapse class="rounded-lg mt-2 mb-2 p-2 space-y-2">
                            <a href="{{ route('manage.users') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('manage.users') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Manage Users
                            </a>
                        </div>

                    </div>

                    <!-- Reports Dropdown -->
                    <div class="space-y-1 mt-4">
                        <button @click="reportsOpen = !reportsOpen"
                            class="group w-full flex items-center justify-between px-6 py-4 rounded-lg text-base nav-item {{ request()->routeIs('reports.*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }} focus:outline-none focus:ring-2 focus:ring-red-200">
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
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('reports.summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Coordinator Report
                            </a>
                            <a href="{{ route('reports.tellers') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('reports.tellers') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Teller Report
                            </a>
                            <a href="{{ route('reports.tellers-summary') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('reports.tellers-summary') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Sales Summary
                            </a>
                            <a href="{{ route('reports.winning-report') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('reports.winning-report') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Winning Report
                            </a>
                            <a href="{{ route('manage.receipts') }}"
                                class="block pl-12 pr-5 py-2.5 rounded-lg nav-item border-l-4 {{ request()->routeIs('manage.receipts*') ? 'text-red-600 bg-gradient-to-r from-red-50 to-red-100 font-medium border-red-500' : 'text-gray-600 hover:bg-red-50 hover:text-red-600 border-transparent' }}">
                                Receipts
                            </a>
                        </div>
                    </div>



                    <h3 class="px-5 py-3 mt-6 text-sm font-semibold text-gray-500 uppercase tracking-wider">GAME
                        MANAGEMENT</h3>

                    <!-- Draws Link -->
                    <a href="{{ route('manage.draws') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.draws*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        <span>Draws</span>
                    </a>
{{--
                    <a href="{{ route('manage.winning-amounts') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.winning-amounts') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Winning Amounts</span>
                    </a> --}}

                    <!-- Bet Ratio Link -->
                    {{-- <a href="{{ route('manage.bet-ratios') }}"
                        class="group flex items-center px-6 py-4 rounded-lg text-base transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.bet-ratios*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'text-gray-700 hover:bg-red-50 hover:text-red-600' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                        </svg>
                        <span>Bet Ratios</span>
                    </a> --}}

                    <!-- Sold Out Link -->
                    <a href="{{ route('manage.sold-out-numbers') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.sold-out-numbers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <span>Sold Out Numbers</span>
                    </a>

                    <!-- Low Win Link -->
                    <a href="{{ route('manage.low-win-numbers') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.low-win-numbers*') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                        </svg>
                        <span>Low Win Numbers</span>
                    </a>

                    <!-- Bets Link -->
                    <a href="{{ route('manage.bets') }}"
                        class="group flex items-center px-5 py-3.5 rounded-lg text-base text-gray-700 transition-all duration-300 ease-in-out nav-item {{ request()->routeIs('manage.bets') ? 'text-white bg-gradient-to-r from-red-500 to-pink-500 font-medium' : 'hover:text-red-600 hover:bg-red-50 hover:border-l-4 hover:border-red-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        <span>All Bets</span>
                    </a>


                </nav>
            </aside>
        </div>
</div>
</div>
