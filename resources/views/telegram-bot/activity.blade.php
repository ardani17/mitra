<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Telegram Bot - Activity Monitor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Activities
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        {{ $stats['total'] ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <i class="fas fa-calendar-day text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Today
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        {{ $stats['today'] ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <i class="fas fa-file-upload text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Files
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        {{ $stats['total_files'] ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Active Users
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900">
                                        {{ $stats['active_users'] ?? 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Queue Stats -->
            @if(isset($uploadQueue))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-cloud-upload-alt text-blue-500 mr-2"></i>
                        Upload Queue Status
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Pending</span>
                            <p class="text-xl font-semibold text-yellow-600">{{ $uploadQueue['pending'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Processing</span>
                            <p class="text-xl font-semibold text-blue-600">{{ $uploadQueue['processing'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Completed</span>
                            <p class="text-xl font-semibold text-green-600">{{ $uploadQueue['completed'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Failed</span>
                            <p class="text-xl font-semibold text-red-600">{{ $uploadQueue['failed'] ?? 0 }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Success Rate</span>
                            <p class="text-xl font-semibold text-gray-900">{{ $stats['success_rate'] ?? 0 }}%</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-history text-gray-500 mr-2"></i>
                        Recent Activities
                    </h3>

                    @if($activities->isEmpty())
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">No activities recorded yet</p>
                            <p class="text-sm text-gray-400 mt-2">Bot activities will appear here once users start interacting</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Time
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Details
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Project
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($activities as $activity)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->created_at->format('H:i:s') }}<br>
                                            <span class="text-xs">{{ $activity->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if($activity->telegram_username)
                                                    @{{ $activity->telegram_username }}
                                                @else
                                                    User {{ $activity->telegram_user_id }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $typeIcon = 'fa-circle';
                                                $typeColor = 'text-gray-400';
                                                switch($activity->message_type) {
                                                    case 'command':
                                                        $typeIcon = 'fa-terminal';
                                                        $typeColor = 'text-purple-500';
                                                        break;
                                                    case 'file':
                                                    case 'document':
                                                        $typeIcon = 'fa-file';
                                                        $typeColor = 'text-blue-500';
                                                        break;
                                                    case 'photo':
                                                        $typeIcon = 'fa-image';
                                                        $typeColor = 'text-green-500';
                                                        break;
                                                    case 'video':
                                                        $typeIcon = 'fa-video';
                                                        $typeColor = 'text-red-500';
                                                        break;
                                                    case 'text':
                                                        $typeIcon = 'fa-comment';
                                                        $typeColor = 'text-gray-500';
                                                        break;
                                                }
                                            @endphp
                                            <div class="flex items-center">
                                                <i class="fas {{ $typeIcon }} {{ $typeColor }} mr-2"></i>
                                                <span class="text-sm text-gray-900">{{ ucfirst($activity->message_type) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                @if($activity->command)
                                                    <code class="bg-gray-100 px-1 py-0.5 rounded">{{ $activity->command }}</code>
                                                @elseif($activity->file_name)
                                                    {{ $activity->file_name }}
                                                    @if($activity->file_size)
                                                        <span class="text-xs text-gray-500">({{ $activity->formatted_file_size }})</span>
                                                    @endif
                                                @elseif($activity->message_text)
                                                    {{ Str::limit($activity->message_text, 50) }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($activity->project)
                                                <div class="text-sm text-gray-900">{{ $activity->project->code }}</div>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($activity->status == 'success')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Success
                                                </span>
                                            @elseif($activity->status == 'failed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Failed
                                                </span>
                                            @elseif($activity->status == 'pending')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ ucfirst($activity->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $activities->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>