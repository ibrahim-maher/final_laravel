{{-- resources/views/visitor-logs/reports/summary.blade.php --}}
<div class="space-y-6">
    <!-- Report Header -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Summary Report</h2>
            <p class="text-gray-600">
                Period: {{ $data['period']['from'] }} - {{ $data['period']['to'] }} ({{ $data['period']['days'] }} days)
            </p>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Logs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($data['totals']['total_logs']) }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-list text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Check-ins</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($data['totals']['total_checkins']) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-sign-in-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Check-outs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($data['totals']['total_checkouts']) }}</p>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-sign-out-alt text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Unique Visitors</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($data['totals']['unique_visitors']) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-users text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Duration Metrics -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Duration Analysis</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ number_format($data['duration']['total_hours'], 1) }}h</div>
                <div class="text-sm text-gray-600">Total Hours</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ number_format($data['duration']['total_minutes']) }}m</div>
                <div class="text-sm text-gray-600">Total Minutes</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ number_format($data['duration']['average_minutes']) }}m</div>
                <div class="text-sm text-gray-600">Average per Visit</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ number_format($data['duration']['average_hours'], 2) }}h</div>
                <div class="text-sm text-gray-600">Average Hours</div>
            </div>
        </div>
    </div>

    <!-- Daily Averages -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Daily Averages</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($data['daily_averages']['logs_per_day'], 1) }}</div>
                <div class="text-sm text-gray-600">Logs per Day</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($data['daily_averages']['checkins_per_day'], 1) }}</div>
                <div class="text-sm text-gray-600">Check-ins per Day</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($data['daily_averages']['visitors_per_day'], 1) }}</div>
                <div class="text-sm text-gray-600">Visitors per Day</div>
            </div>
        </div>
    </div>

    <!-- Performance Indicators -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Performance Indicators</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-700">Completion Rate</span>
                <div class="flex items-center space-x-3">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $data['totals']['completion_rate'] }}%"></div>
                    </div>
                    <span class="text-lg font-bold text-green-600">{{ $data['totals']['completion_rate'] }}%</span>
                </div>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span class="font-medium text-gray-700">Return Visitor Rate</span>
                <div class="flex items-center space-x-3">
                    <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ max(0, (($data['totals']['total_logs'] - $data['totals']['unique_visitors']) / max($data['totals']['unique_visitors'], 1)) * 100) }}%"></div>
                    </div>
                    <span class="text-lg font-bold text-blue-600">
                        {{ number_format(max(0, (($data['totals']['total_logs'] - $data['totals']['unique_visitors']) / max($data['totals']['unique_visitors'], 1)) * 100), 1) }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Summary Table</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daily Average</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total Activities</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['total_logs']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['daily_averages']['logs_per_day'], 1) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">100%</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Check-ins</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['total_checkins']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['daily_averages']['checkins_per_day'], 1) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data['totals']['total_logs'] > 0 ? number_format(($data['totals']['total_checkins'] / $data['totals']['total_logs']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Check-outs</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['total_checkouts']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format(($data['totals']['total_checkouts'] / $data['period']['days']), 1) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data['totals']['total_logs'] > 0 ? number_format(($data['totals']['total_checkouts'] / $data['totals']['total_logs']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Unique Visitors</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['totals']['unique_visitors']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($data['daily_averages']['visitors_per_day'], 1) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data['totals']['total_logs'] > 0 ? number_format(($data['totals']['unique_visitors'] / $data['totals']['total_logs']) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>