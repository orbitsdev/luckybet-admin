 <header class="bg-white shadow-sm h-16 fixed top-0 left-0 right-0 z-[-1]">
        <div class="flex items-center justify-between h-full px-6">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = !sidebarOpen" aria-controls="sidebar"
                    class="p-2 rounded-lg text-gray-700 header-btn hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <!-- Logo in header -->
                <div class="flex items-center">
                    <img src="{{ asset('assets/logo.png') }}" alt="LuckyBet Logo" class="h-24 w-auto">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2 rounded-lg text-gray-600 header-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span
                            class="absolute top-1 right-1 bg-[#FC0204] text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                    </button>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center space-x-3 focus:outline-none transition-colors duration-300 ease-in-out">
                        <div
                            class="text-gray-700 font-semibold hidden sm:block hover:text-[#FC0204] transition-colors duration-300 ease-in-out">
                            Super Admin</div>
                        <div
                            class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-[#FC0204] hover:ring-4 transition-all duration-300 ease-in-out">
                            <img src="https://ui-avatars.com/api/?name=Super+Admin&color=7F9CF5&background=EBF4FF"
                                alt="Profile" class="h-full w-full object-cover">
                        </div>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-40"
                        style="display: none;">
                        <a href="#"
                            class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span>Profile</span>
                            </div>
                        </a>
                        <a href="#"
                            class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <span>Settings</span>
                            </div>
                        </a>
                        <div class="border-t-2 border-[#FC0204] my-2 opacity-80 mx-2 rounded-full"></div>
                        <a href="#"
                            class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                </svg>
                                <span>Logout</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
    </header>