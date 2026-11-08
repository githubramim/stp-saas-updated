@php
    $currentProject = null;
    $userProjects = collect();
    $isTrainer = Auth::check() && Auth::user()->role === 'trainer';

    if (Auth::check() && Auth::user()->institute_id) {
        $userProjects = \App\Models\Project::where('institute_id', Auth::user()->institute_id)->get();
        $currentProjectId = session('current_project_id');
        $currentProject = $userProjects->firstWhere('id', $currentProjectId);
    }
@endphp

<!-- Sidebar Mobile Backdrop -->
<div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false" 
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"></div>

<!-- Sidebar Fixed Left Container -->
<aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed top-0 left-0 z-50 h-screen w-64 bg-slate-950 text-slate-100 flex flex-col justify-between transition-transform duration-300 ease-in-out shadow-2xl border-r border-slate-800/80 select-none lg:translate-x-0">
    
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Brand Logo Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800/80 bg-slate-950/90 shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-decoration-none group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 flex items-center justify-center font-extrabold text-base text-white shadow-md shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                    STP
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm tracking-tight text-white leading-none">STP SaaS</span>
                    <span class="text-[10px] text-indigo-400 font-mono mt-1 font-medium">
                        {{ Auth::user()->institute?->code ?? 'PLATFORM' }}
                    </span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Role Badge Pill -->
        <div class="px-3 py-2.5 bg-slate-900/60 border-b border-slate-800/80 shrink-0">
            @if($isTrainer)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-500/10 border border-purple-500/20 text-purple-300 text-xs font-semibold">
                    <i class="bi bi-person-workspace text-purple-400"></i>
                    <span>Trainer Access</span>
                </div>
            @else
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-semibold">
                    <i class="bi bi-shield-lock-fill text-indigo-400"></i>
                    <span>Institute Admin</span>
                </div>
            @endif
        </div>

        <!-- Navigation Scrollable Item List -->
        <nav class="flex-1 px-3 py-3 space-y-1 overflow-y-auto custom-sidebar-scroll">
            
            <div class="px-2 pt-1 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                <i class="bi bi-grid-fill text-slate-500"></i>
                <span>Core Modules</span>
            </div>

            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 item-icon text-indigo-400"></i>
                <span class="item-text">{{ __('Dashboard') }}</span>
            </a>

            @if(!$isTrainer)
                <!-- 2. Projects -->
                <a href="{{ route('projects.index') }}" class="sidebar-item {{ request()->routeIs('projects.*') && !request()->routeIs('projects.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open item-icon text-blue-400"></i>
                    <span class="item-text">{{ __('Projects') }}</span>
                </a>

                <!-- 3. Courses -->
                <a href="{{ route('courses.index') }}" class="sidebar-item {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text item-icon text-teal-400"></i>
                    <span class="item-text">{{ __('Courses') }}</span>
                </a>
            @endif

            <!-- 4. Batches -->
            <a href="{{ route('batches.index') }}" class="sidebar-item {{ request()->routeIs('batches.*') ? 'active' : '' }}">
                <i class="bi bi-people item-icon text-violet-400"></i>
                <span class="item-text">{{ $isTrainer ? __('My Batches') : __('Batches') }}</span>
            </a>

            @if(!$isTrainer)
                <!-- 5. Applications -->
                <a href="{{ route('admissions.applications') }}" class="sidebar-item {{ request()->routeIs('admissions.applications') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-person item-icon text-amber-400"></i>
                    <span class="item-text">{{ __('Applications') }}</span>
                </a>
            @endif

            <!-- 6. Students -->
            <a href="{{ route('admissions.students') }}" class="sidebar-item {{ request()->routeIs('admissions.students') ? 'active' : '' }}">
                <i class="bi bi-mortarboard item-icon text-emerald-400"></i>
                <span class="item-text">{{ $isTrainer ? __('Enrolled Students') : __('Students') }}</span>
            </a>

            <!-- 7. Attendance -->
            <a href="{{ route('attendance.index') }}" class="sidebar-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check item-icon text-rose-400"></i>
                <span class="item-text">{{ __('Attendance') }}</span>
            </a>

            <!-- 7.1 Class Routine -->
            <a href="{{ route('schedules.index') }}" class="sidebar-item {{ request()->routeIs('schedules.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history item-icon text-cyan-400"></i>
                <span class="item-text">{{ __('Routine') }}</span>
            </a>

            <!-- 7.2 Exams -->
            <a href="{{ route('exams.index') }}" class="sidebar-item {{ request()->routeIs('exams.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-check item-icon text-indigo-400"></i>
                <span class="item-text">{{ __('Exams') }}</span>
            </a>

            <a href="{{ route('certificates.index') }}" class="sidebar-item {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
                <i class="bi bi-award item-icon text-orange-400"></i>
                <span class="item-text">{{ __('Certificates') }}</span>
            </a>

            @if(!$isTrainer)
                <!-- 8. Finance -->
                <a href="{{ route('transactions.index') }}" class="sidebar-item {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2 item-icon text-emerald-400"></i>
                    <span class="item-text">{{ __('Finance') }}</span>
                </a>
            @endif

            <!-- 9. Resources -->
            <a href="{{ route('resources.index') }}" class="sidebar-item {{ request()->routeIs('resources.*') ? 'active' : '' }}">
                <i class="bi bi-cpu item-icon text-cyan-400"></i>
                <span class="item-text">{{ __('Resources') }}</span>
            </a>

            <div class="px-2 pt-4 pb-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                <i class="bi bi-chat-square-dots text-slate-500"></i>
                <span>Communication</span>
            </div>

            <!-- 10. Notifications & SMS -->
            <a href="{{ route('notifications.index') }}" class="sidebar-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-send item-icon text-indigo-400"></i>
                <span class="item-text">{{ __('Notifications & SMS') }}</span>
            </a>

            <!-- 10.1 Notices -->
            <a href="{{ route('notices.index') }}" class="sidebar-item {{ request()->routeIs('notices.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone item-icon text-pink-400"></i>
                <span class="item-text">{{ __('Notices') }}</span>
            </a>

            <!-- 11. Study Materials -->
            <a href="{{ route('study-materials.index') }}" class="sidebar-item {{ request()->routeIs('study-materials.*') ? 'active' : '' }}">
                <i class="bi bi-cloud-arrow-down item-icon text-fuchsia-400"></i>
                <span class="item-text">{{ __('Study Materials') }}</span>
            </a>

            <!-- 12. Internal Evaluations -->
            <a href="{{ route('evaluations.index') }}" class="sidebar-item {{ request()->routeIs('evaluations.*') ? 'active' : '' }}">
                <i class="bi bi-star item-icon text-yellow-400"></i>
                <span class="item-text">{{ __('Evaluations') }}</span>
            </a>
        </nav>

        <!-- Sidebar Footer Action -->
        <div class="p-3 border-t border-slate-800/80 bg-slate-950/90 shrink-0">
            <a href="{{ route('portal.index') }}" target="_blank" 
               class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs text-slate-300 text-decoration-none transition">
                <span class="flex items-center gap-2 font-medium">
                    <i class="bi bi-globe text-indigo-400"></i>
                    <span>Student Portal</span>
                </span>
                <i class="bi bi-box-arrow-up-right text-slate-500 text-[10px]"></i>
            </a>
        </div>
    </div>
</aside>

<style>
    /* Clean, standalone CSS for Sidebar Links to prevent Bootstrap conflict */
    .sidebar-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.75rem !important;
        padding: 0.6rem 0.85rem !important;
        margin-bottom: 0.2rem !important;
        border-radius: 0.65rem !important;
        color: #94a3b8 !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: all 0.2s ease-in-out !important;
    }
    .sidebar-item:hover {
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.06) !important;
    }
    .sidebar-item.active {
        color: #ffffff !important;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
        font-weight: 600 !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35) !important;
    }
    .sidebar-item.active .item-icon {
        color: #ffffff !important;
    }
    .item-icon {
        font-size: 1.1rem !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 1.5rem !important;
    }

    /* Scrollbar */
    .custom-sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-sidebar-scroll::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 4px;
    }
</style>
