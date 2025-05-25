<div
    x-data="{
        sidebarOpen: true,
        usersOpen: false,
        reportsOpen: false,
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            console.log('Sidebar toggled:', this.sidebarOpen);
        },
        init() {
            console.log('Dashboard initialized');
            if (window.innerWidth < 1024) {
                this.sidebarOpen = false;
            }
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = true;
                }
            });
        }
    }"
    class="min-h-screen flex flex-col"
>
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-3 bg-white shadow-lg z-40">
        <div class="flex items-center space-x-4">
            <button @click="toggleSidebar()" aria-controls="sidebar" class="p-2 rounded-lg text-gray-700 header-btn hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>
        <div class="flex items-center gap-4">
            <!-- Notifications -->
            <div class="relative">
                <button class="p-2 rounded-lg text-gray-600 header-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <span class="absolute top-1 right-1 bg-[#FC0204] text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                </button>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none transition-colors duration-300 ease-in-out">
                    <div class="text-gray-700 font-semibold hidden sm:block hover:text-[#FC0204] transition-colors duration-300 ease-in-out">Super Admin</div>
                    <div class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-[#FC0204] hover:ring-4 transition-all duration-300 ease-in-out">
                        <img src="https://ui-avatars.com/api/?name=Super+Admin&color=7F9CF5&background=EBF4FF" alt="Profile" class="h-full w-full object-cover">
                    </div>
                </button>

                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50" style="display: none;">
                    <a href="#" class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>Profile</span>
                        </div>
                    </a>
                    <a href="#" class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Settings</span>
                        </div>
                    </a>
                    <div class="border-t-2 border-[#FC0204] my-2 opacity-80 mx-2 rounded-full"></div>
                    <a href="#" class="block px-4 py-2 text-gray-700 rounded-md transition-colors duration-300 ease-in-out hover:bg-gradient-to-r hover:from-[#FC0204] hover:to-[#ff367a] hover:text-white hover:shadow-md">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                            </svg>
                            <span>Logout</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </header>
    <!-- Sidebar and Main Content -->
    <div class="flex flex-1 overflow-hidden bg-white relative w-full">
        <!-- Sidebar with Alpine.js for interactivity -->
        <!-- Mobile backdrop overlay -->
        <div x-show="sidebarOpen && window.innerWidth < 1024"
             x-cloak
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-gray-700 bg-opacity-70 backdrop-blur-sm"></div>

        <!-- Sidebar navigation -->
        <aside
               x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="opacity-0 -translate-x-full"
               x-transition:enter-end="opacity-100 translate-x-0"
               x-transition:leave="transition ease-in duration-300"
               x-transition:leave-start="opacity-100 translate-x-0"
               x-transition:leave-end="opacity-0 -translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-64 overflow-y-auto bg-white lg:static lg:inset-auto shadow-lg">

            <nav class="px-3  border-t-4 border-red-400 py-4 ">
                <a href="#" class="group flex items-center px-5 py-3.5 rounded-lg font-bold text-base text-gray-700 nav-item active">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Users Dropdown -->
                <div class="space-y-1 mt-4">
                    <button @click="usersOpen = !usersOpen; console.log('Users dropdown toggled:', usersOpen)" class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base text-gray-700 nav-item hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <span>Users</span>
                        </div>
                        <svg :class="usersOpen ? 'transform rotate-90' : ''" class="w-4 h-4 transition-transform duration-300 ease-in-out" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                    <div x-cloak x-show="usersOpen" class="pl-6 mt-1 space-y-1">
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Coordinators</span>
                        </a>
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Tellers</span>
                        </a>
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Customers</span>
                        </a>
                    </div>
                </div>

                <!-- Reports Dropdown -->
                <div class="space-y-1 mt-4">
                    <button @click="reportsOpen = !reportsOpen; console.log('Reports dropdown toggled:', reportsOpen)" class="group w-full flex items-center justify-between px-5 py-3.5 rounded-lg text-base text-gray-700 nav-item hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            <span>Reports</span>
                        </div>
                        <svg :class="reportsOpen ? 'transform rotate-90' : ''" class="w-4 h-4 transition-transform duration-300 ease-in-out" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                    <div x-cloak x-show="reportsOpen" class="pl-6 mt-1 space-y-1">
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Coordinator Report</span>
                        </a>
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Teller Report</span>
                        </a>
                        <a href="#" class="group flex items-center px-4 py-2.5 text-sm text-gray-600 rounded-md nav-item hover:pl-5 transition-all duration-200">
                            <span class="w-1.5 h-1.5 rounded-full opacity-0 group-hover:opacity-100 bg-primary-500 mr-3 transition-all duration-200"></span>
                            <span class="font-medium">Sales Summary</span>
                        </a>
                    </div>
                </div>

                <!-- Game Management Section -->
                <div class="px-4 py-2 text-lg font-heading font-semibold uppercase tracking-wider text-gray-600 mt-6 mb-1">Game Management</div>
                <div class="pt-1"></div>

                <!-- Draw Link -->
                <a href="#" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <span>Draws</span>
                </a>

                <!-- Bet Ratio Link -->
                <a href="#" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                    </svg>
                    <span>Bet Ratios</span>
                </a>

                <!-- Sold Out Link -->
                <a href="#" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <span>Sold Out Numbers</span>
                </a>

                <!-- Low Win Link -->
                <a href="#" class="group flex items-center px-5 py-3.5 rounded-lg text-base nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                    </svg>
                    <span>Low Win Numbers</span>
                </a>
            </nav>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 p-8 bg-white overflow-y-auto transition-all duration-300" :class="{'ml-0 w-full': !sidebarOpen, 'ml-0 lg:ml-64 w-full': sidebarOpen}">

        </main>
    </div>
</div>
