
<div class="min-h-screen bg-gradient-to-br from-[#FC0204] via-[#ff3848] to-[#ff7075] flex flex-col" x-data="{ sidebarOpen: false, profileDropdownOpen: false }">
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-3 bg-white shadow-lg z-20">
        <div class="flex items-center space-x-4">
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg bg-[#FC0204] text-white hover:bg-[#d10000] transform hover:scale-105 transition-all duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <span class="text-xl font-bold text-[#FC0204] tracking-widest">Lucky Bet Admin</span>
        </div>
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <div class="relative">
                <button class="p-2 text-gray-600 hover:text-[#FC0204] transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-1 right-1 bg-[#FC0204] text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                </button>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
                    <div class="text-gray-700 font-semibold hidden sm:block">Super Admin</div>
                    <div class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-[#FC0204] hover:ring-4 transition-all duration-200">
                        <img src="https://ui-avatars.com/api/?name=Super+Admin&color=7F9CF5&background=EBF4FF" alt="Profile" class="h-full w-full object-cover">
                    </div>
                </button>

                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50" style="display: none;">
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-[#ffeeee] hover:text-[#FC0204] transition-colors duration-200">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profile</span>
                        </div>
                    </a>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-[#ffeeee] hover:text-[#FC0204] transition-colors duration-200">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Settings</span>
                        </div>
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-[#ffeeee] hover:text-[#FC0204] transition-colors duration-200">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <!-- Sidebar -->
    <div x-data="{ sidebarOpen: false }" class="flex flex-1">
        <aside :class="sidebarOpen ? 'block' : 'hidden md:block'"
               class="w-64 bg-white border-r border-gray-200 flex-shrink-0 md:block transition-all">
            <nav class="flex flex-col gap-1 py-8">
                <a href="#" class="flex items-center px-6 py-3 text-[#FC0204] bg-gray-100 font-bold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0H7v6m0 0l-2 2m0 0l-7-7 7 7"/>
                    </svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7v6h6m-6 0H8v6m0 0l-2 2m0 0l-7-7 7 7"/>
                    </svg>
                    Users
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Management</a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Reports</a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Draw</a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Bet Ratio</a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Sold Out</a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 transition">Low Win</a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-8 bg-gradient-to-br from-white via-red-50 to-white overflow-y-auto">
            
        </main>
    </div>
</div>
