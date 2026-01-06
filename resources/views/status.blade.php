<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glasgow Subway Status - Unofficial Service Updates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'subway-orange': '#ff6200',
                        'subway-inner': '#3D3D3C',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .subway-pattern {
            background-image: 
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255, 98, 0, 0.03) 10px, rgba(255, 98, 0, 0.03) 20px);
        }
    </style>
</head>
<body class="bg-gray-50 subway-pattern">
    <div class="min-h-screen">
        <!-- Hero Header -->
        <header class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-subway-inner via-gray-800 to-subway-orange opacity-95"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
                <div class="text-center">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <svg class="w-12 h-12 text-subway-orange" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        <h1 class="text-4xl sm:text-5xl font-extrabold text-white">
                            Glasgow Subway
                        </h1>
                    </div>
                    <p class="text-xl text-gray-200 font-medium">Live Service Status</p>
                    <p class="mt-2 text-sm text-gray-300">Unofficial real-time monitoring</p>
                    
                    <!-- Live indicator -->
                    <div class="mt-6 inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-white text-sm font-medium">Updates every 10 minutes</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Development Warning Banner -->
        <div class="bg-gradient-to-r from-yellow-500 via-orange-500 to-red-500 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="flex-shrink-0 mt-0.5 sm:mt-0">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base sm:text-lg font-bold mb-1">⚠️ Beta Version - Under Development</h3>
                        <p class="text-sm sm:text-base font-medium opacity-95 leading-snug">
                            This site is currently in development and data may not be accurate. Always verify service status with 
                            <a href="https://www.spt.co.uk/service-updates/" target="_blank" rel="noopener" class="underline hover:text-yellow-100 font-bold">official SPT channels</a> 
                            or <a href="https://twitter.com/GLASubwayTravel" target="_blank" rel="noopener" class="underline hover:text-yellow-100 font-bold">@GLASubwayTravel</a> before travel.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="app">
                <!-- Loading State -->
                <div id="loading" class="text-center py-20">
                    <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-subway-orange border-t-transparent"></div>
                    <p class="mt-6 text-lg text-gray-600 font-medium">Loading live status...</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="content" class="hidden">
                    <!-- Staleness Warning -->
                    <div id="staleWarning" class="hidden mb-6 bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 rounded-r-lg p-5 shadow-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-bold text-yellow-900 mb-1">Data May Be Outdated</h3>
                                <p class="text-sm text-yellow-800">
                                    Last updated over 10 minutes ago. Information may be delayed or incorrect; check official SPT channels before travel.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Cards -->
                    <div class="grid lg:grid-cols-2 gap-8 mb-10">
                        <!-- Inner Circle -->
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover border border-gray-100">
                            <div class="relative px-8 py-6" style="background: linear-gradient(135deg, #3D3D3C 0%, #1a1a1a 100%);">
                                <div class="absolute top-0 right-0 w-32 h-32 opacity-10">
                                    <svg viewBox="0 0 100 100" fill="currentColor" class="text-white">
                                        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="none"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-white relative z-10">Inner Circle</h2>
                                <p class="text-gray-300 text-sm mt-1 relative z-10">Anticlockwise</p>
                            </div>
                            <div class="p-8">
                                <div class="mb-6">
                                    <span id="innerStatus" class="inline-flex items-center px-5 py-2.5 rounded-xl text-base font-bold shadow-md">
                                        Unknown
                                    </span>
                                </div>
                                <p id="innerMessage" class="text-gray-700 text-lg font-medium mb-4 leading-relaxed">Loading...</p>
                                <div class="flex items-center text-sm text-gray-500 pt-4 border-t border-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span id="innerUpdated">Last updated: -</span>
                                </div>
                            </div>
                        </div>

                        <!-- Outer Circle -->
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover border border-gray-100">
                            <div class="relative px-8 py-6" style="background: linear-gradient(135deg, #ff6200 0%, #d44f00 100%);">
                                <div class="absolute top-0 right-0 w-32 h-32 opacity-10">
                                    <svg viewBox="0 0 100 100" fill="currentColor" class="text-white">
                                        <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="none"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-white relative z-10">Outer Circle</h2>
                                <p class="text-orange-100 text-sm mt-1 relative z-10">Clockwise</p>
                            </div>
                            <div class="p-8">
                                <div class="mb-6">
                                    <span id="outerStatus" class="inline-flex items-center px-5 py-2.5 rounded-xl text-base font-bold shadow-md">
                                        Unknown
                                    </span>
                                </div>
                                <p id="outerMessage" class="text-gray-700 text-lg font-medium mb-4 leading-relaxed">Loading...</p>
                                <div class="flex items-center text-sm text-gray-500 pt-4 border-t border-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span id="outerUpdated">Last updated: -</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Checked Info -->
                    <div class="bg-white rounded-xl shadow-md p-5 mb-10 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 rounded-lg p-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">System Last Updated</p>
                                    <p class="text-base font-bold text-gray-900" id="lastChecked">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Updates -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                        <div class="px-8 py-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">Service Updates</h2>
                                    <p class="text-sm text-gray-600 mt-1">All SPT disruptions and notices</p>
                                </div>
                                <div class="bg-subway-orange text-white px-4 py-2 rounded-lg font-bold text-sm">
                                    LIVE
                                </div>
                            </div>
                        </div>
                        <div id="updatesList" class="divide-y divide-gray-100">
                            <!-- Updates will be inserted here -->
                        </div>
                        <div id="noUpdates" class="hidden p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 font-medium">No recent updates available</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3 text-lg">⚠️ Important Disclaimer</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p>This is an <strong>unofficial</strong> service status tool. Not affiliated with or endorsed by SPT (Strathclyde Partnership for Transport).</p>
                            <p>Information may be delayed, incomplete, or incorrect. Always verify with <a href="https://www.spt.co.uk/travel-with-spt/subway/" target="_blank" class="text-subway-orange hover:underline font-medium">official SPT channels</a> before making travel decisions.</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3 text-lg">📍 Official Resources</h3>
                        <div class="space-y-2">
                            <a href="https://www.spt.co.uk/travel-with-spt/subway/" target="_blank" class="block text-sm text-subway-orange hover:underline font-medium">
                                → SPT Official Subway Page
                            </a>
                            <a href="https://twitter.com/GLASubwayTravel" target="_blank" class="block text-sm text-subway-orange hover:underline font-medium">
                                → @GLASubwayTravel on Twitter
                            </a>
                        </div>
                        <p class="text-xs text-gray-500 mt-4">Data updates every 10 minutes • Built with Laravel</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Status badge classes
        const statusClasses = {
            running: 'bg-gradient-to-r from-green-400 to-green-500 text-white ring-4 ring-green-100',
            suspended: 'bg-gradient-to-r from-red-500 to-red-600 text-white ring-4 ring-red-100',
            disrupted: 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white ring-4 ring-yellow-100',
            unknown: 'bg-gradient-to-r from-gray-400 to-gray-500 text-white ring-4 ring-gray-100'
        };

        const statusText = {
            running: '✓ Running',
            suspended: '✕ Suspended',
            disrupted: '⚠ Disrupted',
            unknown: '? Unknown'
        };

        // Format date
        function formatDate(isoString) {
            if (!isoString) return 'Never';
            const date = new Date(isoString);
            return date.toLocaleString('en-GB', { 
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Fetch and display status
        async function loadStatus() {
            try {
                const response = await fetch('/api/status');
                const data = await response.json();

                // Update inner circle
                const innerStatus = document.getElementById('innerStatus');
                innerStatus.textContent = statusText[data.inner.status] || '? Unknown';
                innerStatus.className = 'inline-flex items-center px-5 py-2.5 rounded-xl text-base font-bold shadow-md ' + 
                    (statusClasses[data.inner.status] || statusClasses.unknown);
                document.getElementById('innerMessage').textContent = data.inner.message || 'No information available';
                document.getElementById('innerUpdated').textContent = formatDate(data.inner.updated_at);

                // Update outer circle
                const outerStatus = document.getElementById('outerStatus');
                outerStatus.textContent = statusText[data.outer.status] || '? Unknown';
                outerStatus.className = 'inline-flex items-center px-5 py-2.5 rounded-xl text-base font-bold shadow-md ' + 
                    (statusClasses[data.outer.status] || statusClasses.unknown);
                document.getElementById('outerMessage').textContent = data.outer.message || 'No information available';
                document.getElementById('outerUpdated').textContent = formatDate(data.outer.updated_at);

                // Update last checked
                document.getElementById('lastChecked').textContent = formatDate(data.meta.last_checked_at);

                // Show staleness warning if needed
                if (data.meta.stale) {
                    document.getElementById('staleWarning').classList.remove('hidden');
                }

            } catch (error) {
                console.error('Error loading status:', error);
                alert('Failed to load status information. Please try again later.');
            }
        }

        // Fetch and display recent updates
        async function loadUpdates() {
            try {
                const response = await fetch('/api/updates?limit=20');
                const data = await response.json();

                const updatesList = document.getElementById('updatesList');
                const noUpdates = document.getElementById('noUpdates');

                if (data.updates.length === 0) {
                    updatesList.innerHTML = '';
                    noUpdates.classList.remove('hidden');
                } else {
                    noUpdates.classList.add('hidden');
                    
                    // Get badge color for disruption type
                    const getTypeBadge = (type) => {
                        const badges = {
                            'subway': 'bg-subway-orange text-white',
                            'bus': 'bg-blue-100 text-blue-800',
                            'train': 'bg-green-100 text-green-800',
                            'rail': 'bg-purple-100 text-purple-800'
                        };
                        return badges[type.toLowerCase()] || 'bg-gray-100 text-gray-800';
                    };
                    
                    updatesList.innerHTML = data.updates.map(update => `
                        <div class="p-8 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start gap-6">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3 mb-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${getTypeBadge(update.disruption_type)}">
                                            ${escapeHtml(update.disruption_type).toUpperCase()}
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight">${escapeHtml(update.title)}</h3>
                                    ${update.snippet ? `<p class="text-gray-600 mb-4 leading-relaxed">${escapeHtml(update.snippet)}</p>` : ''}
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        ${update.published_date ? `
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Published: ${update.published_date}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }

            } catch (error) {
                console.error('Error loading updates:', error);
                document.getElementById('updatesList').innerHTML = `
                    <div class="p-6 text-center text-red-600">
                        Failed to load updates. Please try again later.
                    </div>
                `;
            }
        }

        // HTML escape helper
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize
        async function init() {
            await Promise.all([loadStatus(), loadUpdates()]);
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('content').classList.remove('hidden');
        }

        // Load on page ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Auto-refresh every 60 seconds
        setInterval(() => {
            loadStatus();
            loadUpdates();
        }, 60000);
    </script>
</body>
</html>
