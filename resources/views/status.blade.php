<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glasgow Subway Status - Unofficial Service Updates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'subway-orange': '#FF6600',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <h1 class="text-3xl font-bold text-gray-900">Glasgow Subway Status</h1>
                <p class="mt-1 text-sm text-gray-600">Unofficial service status monitoring</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="app">
                <!-- Loading State -->
                <div id="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-subway-orange"></div>
                    <p class="mt-4 text-gray-600">Loading status...</p>
                </div>

                <!-- Content (hidden until loaded) -->
                <div id="content" class="hidden">
                    <!-- Staleness Warning -->
                    <div id="staleWarning" class="hidden mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Data may be outdated.</strong> Last updated over 10 minutes ago. Information may be delayed or incorrect; check official channels before travel.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Cards -->
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <!-- Inner Circle -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-subway-orange to-orange-600">
                                <h2 class="text-xl font-bold text-white">Inner Circle</h2>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span id="innerStatus" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold">
                                        Unknown
                                    </span>
                                </div>
                                <p id="innerMessage" class="text-gray-700 mb-2">Loading...</p>
                                <p id="innerUpdated" class="text-xs text-gray-500">Last updated: -</p>
                            </div>
                        </div>

                        <!-- Outer Circle -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-subway-orange to-orange-600">
                                <h2 class="text-xl font-bold text-white">Outer Circle</h2>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span id="outerStatus" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold">
                                        Unknown
                                    </span>
                                </div>
                                <p id="outerMessage" class="text-gray-700 mb-2">Loading...</p>
                                <p id="outerUpdated" class="text-xs text-gray-500">Last updated: -</p>
                            </div>
                        </div>
                    </div>

                    <!-- Last Checked Info -->
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-8">
                        <p class="text-sm text-gray-600">
                            <strong>Last checked:</strong> <span id="lastChecked">-</span>
                        </p>
                    </div>

                    <!-- Recent Updates -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-100 border-b">
                            <h2 class="text-xl font-bold text-gray-900">Recent Service Updates</h2>
                        </div>
                        <div id="updatesList" class="divide-y">
                            <!-- Updates will be inserted here -->
                        </div>
                        <div id="noUpdates" class="hidden p-6 text-center text-gray-500">
                            No recent subway updates available.
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-sm text-gray-600 space-y-2">
                    <p><strong>Disclaimer:</strong> This is an unofficial service status tool. Not affiliated with or endorsed by SPT (Strathclyde Partnership for Transport).</p>
                    <p>Information may be delayed or incorrect. Always check <a href="https://www.spt.co.uk/travel-with-spt/subway/" target="_blank" class="text-subway-orange hover:underline">official SPT channels</a> before travel.</p>
                    <p>Provided as-is without warranties.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Status badge classes
        const statusClasses = {
            running: 'bg-green-100 text-green-800',
            suspended: 'bg-red-100 text-red-800',
            disrupted: 'bg-yellow-100 text-yellow-800',
            unknown: 'bg-gray-100 text-gray-800'
        };

        const statusText = {
            running: 'Running',
            suspended: 'Suspended',
            disrupted: 'Disrupted',
            unknown: 'Unknown'
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
                innerStatus.textContent = statusText[data.inner.status] || 'Unknown';
                innerStatus.className = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ' + 
                    (statusClasses[data.inner.status] || statusClasses.unknown);
                document.getElementById('innerMessage').textContent = data.inner.message || 'No information available';
                document.getElementById('innerUpdated').textContent = 'Last updated: ' + formatDate(data.inner.updated_at);

                // Update outer circle
                const outerStatus = document.getElementById('outerStatus');
                outerStatus.textContent = statusText[data.outer.status] || 'Unknown';
                outerStatus.className = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ' + 
                    (statusClasses[data.outer.status] || statusClasses.unknown);
                document.getElementById('outerMessage').textContent = data.outer.message || 'No information available';
                document.getElementById('outerUpdated').textContent = 'Last updated: ' + formatDate(data.outer.updated_at);

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
                    updatesList.innerHTML = data.updates.map(update => `
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">${escapeHtml(update.title)}</h3>
                                    ${update.snippet ? `<p class="text-gray-700 mb-2">${escapeHtml(update.snippet)}</p>` : ''}
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span>Type: ${escapeHtml(update.disruption_type)}</span>
                                        ${update.published_date ? `<span>Published: ${update.published_date}</span>` : ''}
                                    </div>
                                </div>
                                ${update.url ? `
                                    <a href="${escapeHtml(update.url)}" target="_blank" class="ml-4 text-subway-orange hover:text-orange-700 text-sm font-medium">
                                        View Details →
                                    </a>
                                ` : ''}
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
