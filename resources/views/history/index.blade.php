<x-layout title="Historical Data Analysis" active="history">

    {{-- Chart.js in head --}}
    <x-slot name="head">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    </x-slot>

    {{-- Main Content --}}
    
    {{-- Filter Panel --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filters
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Device</label>
                    <select id="deviceFilter" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                        <option value="" style="background-color: #1e1b4b; color: white;">All Devices</option>
                        @foreach($devices as $device)
                            <option value="{{ $device['id'] }}" style="background-color: #1e1b4b; color: white;">{{ $device['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Data Type</label>
                    <select id="dataTypeFilter" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="handleDataTypeChange()" style="color-scheme: dark;">
                        <option value="sensors" style="background-color: #1e1b4b; color: white;">Sensors</option>
                        <option value="actuators" style="background-color: #1e1b4b; color: white;">Actuators</option>
                    </select>
                </div>
                <div id="sensorTypeFilterContainer">
                    <label class="text-sm text-indigo-300 mb-2 block">Sensor Type</label>
                    <select id="sensorTypeFilter" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                        <option value="" style="background-color: #1e1b4b; color: white;">All Types</option>
                        <option value="temperature" style="background-color: #1e1b4b; color: white;">Temperature</option>
                        <option value="humidity" style="background-color: #1e1b4b; color: white;">Humidity</option>
                        <option value="water_level" style="background-color: #1e1b4b; color: white;">Water Level</option>
                        <option value="soil_moisture" style="background-color: #1e1b4b; color: white;">Soil Moisture</option>
                        <option value="co2_ppm" style="background-color: #1e1b4b; color: white;">PPM</option>
                        <option value="weight" style="background-color: #1e1b4b; color: white;">Weight</option>
                    </select>
                </div>
                <div id="actuatorTypeFilterContainer" style="display: none;">
                    <label class="text-sm text-indigo-300 mb-2 block">Actuator Type</label>
                    <select id="actuatorTypeFilter" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                        <option value="" style="background-color: #1e1b4b; color: white;">All Types</option>
                        <option value="fan" style="background-color: #1e1b4b; color: white;">Fan</option>
                        <option value="heater" style="background-color: #1e1b4b; color: white;">Heater</option>
                        <option value="humidifier" style="background-color: #1e1b4b; color: white;">Humidifier</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">Start Date</label>
                    <input type="date" id="startDate" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                </div>
                <div>
                    <label class="text-sm text-indigo-300 mb-2 block">End Date</label>
                    <input type="date" id="endDate" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" style="color-scheme: dark;">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 mt-4">
                <button onclick="applyFilters()" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300">
                    Apply Filters
                </button>
                <button onclick="resetFilters()" class="w-full sm:w-auto px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-lg transition-all duration-300">
                    Reset
                </button>
                <button onclick="setQuickFilter('today')" class="w-full sm:w-auto px-4 py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">
                    Today
                </button>
                <button onclick="setQuickFilter('week')" class="w-full sm:w-auto px-4 py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">
                    Last 7 Days
                </button>
                <button onclick="setQuickFilter('month')" class="w-full sm:w-auto px-4 py-3 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">
                    Last 30 Days
                </button>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div id="statsSection" class="mb-8 hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white/10 backdrop-blur-xl rounded-xl p-6 border border-white/20">
                <p class="text-sm text-indigo-300 mb-2">Average</p>
                <p id="statAvg" class="text-3xl font-bold text-white">-</p>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-xl p-6 border border-white/20">
                <p class="text-sm text-indigo-300 mb-2">Minimum</p>
                <p id="statMin" class="text-3xl font-bold text-white">-</p>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-xl p-6 border border-white/20">
                <p class="text-sm text-indigo-300 mb-2">Maximum</p>
                <p id="statMax" class="text-3xl font-bold text-white">-</p>
            </div>
            <div class="bg-white/10 backdrop-blur-xl rounded-xl p-6 border border-white/20">
                <p class="text-sm text-indigo-300 mb-2">Total Records</p>
                <p id="statCount" class="text-3xl font-bold text-white">-</p>
            </div>
        </div>
    </div>

    {{-- Chart Visualization --}}
    <div id="chartSection" class="mb-8 hidden">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold text-white mb-6">Trend Visualization</h2>
            <div class="h-96">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="mb-8">
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-6 border border-white/20">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Data Records</h2>
                <div id="loadingIndicator" class="hidden">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Device</th>
                            <th id="typeHeader" class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Sensor Type</th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Value</th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Unit</th>
                            <th id="extraHeader" class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2"></th>
                            <th class="text-left text-sm font-semibold text-indigo-300 pb-3 px-2">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        <tr>
                            <td colspan="5" class="py-8 text-center">
                                <p class="text-gray-400">Apply filters to load data</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="paginationSection" class="mt-6 flex items-center justify-between hidden">
                <div class="text-sm text-indigo-200">
                    <span id="pageInfo">-</span>
                </div>
                <div class="flex gap-2" id="paginationButtons">
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <x-slot name="scripts">
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let currentPage = 1;
            let chart = null;

            function handleDataTypeChange() {
                const dataType = document.getElementById('dataTypeFilter').value;
                const sensorTypeFilterContainer = document.getElementById('sensorTypeFilterContainer');
                const actuatorTypeFilterContainer = document.getElementById('actuatorTypeFilterContainer');
                const sensorTypeFilter = document.getElementById('sensorTypeFilter');
                const actuatorTypeFilter = document.getElementById('actuatorTypeFilter');
                const typeHeader = document.getElementById('typeHeader');
                const extraHeader = document.getElementById('extraHeader');
                
                if (dataType === 'actuators') {
                    // Show actuator filter, hide sensor filter
                    sensorTypeFilterContainer.style.display = 'none';
                    actuatorTypeFilterContainer.style.display = 'block';
                    sensorTypeFilter.value = '';
                    
                    typeHeader.textContent = 'Actuator Type';
                    extraHeader.textContent = 'Status';
                } else {
                    // Show sensor filter, hide actuator filter
                    sensorTypeFilterContainer.style.display = 'block';
                    actuatorTypeFilterContainer.style.display = 'none';
                    actuatorTypeFilter.value = '';
                    
                    typeHeader.textContent = 'Sensor Type';
                    extraHeader.textContent = '';
                }
            }

            function setQuickFilter(type) {
                const today = new Date();
                const endDate = today.toISOString().split('T')[0];
                let startDate = '';

                if (type === 'today') {
                    startDate = endDate;
                } else if (type === 'week') {
                    const weekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    startDate = weekAgo.toISOString().split('T')[0];
                } else if (type === 'month') {
                    const monthAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                    startDate = monthAgo.toISOString().split('T')[0];
                }

                document.getElementById('startDate').value = startDate;
                document.getElementById('endDate').value = endDate;
                applyFilters();
            }

            function resetFilters() {
                document.getElementById('deviceFilter').value = '';
                document.getElementById('dataTypeFilter').value = 'sensors';
                document.getElementById('sensorTypeFilter').value = '';
                document.getElementById('actuatorTypeFilter').value = '';
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                handleDataTypeChange();
                currentPage = 1;
                
                document.getElementById('statsSection').classList.add('hidden');
                document.getElementById('chartSection').classList.add('hidden');
                document.getElementById('paginationSection').classList.add('hidden');
                
                document.getElementById('dataTableBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="py-8 text-center">
                            <p class="text-gray-400">Apply filters to load data</p>
                        </td>
                    </tr>
                `;
            }

            function applyFilters(page = 1) {
                currentPage = page;
                const dataType = document.getElementById('dataTypeFilter').value;
                const params = {
                    device_id: document.getElementById('deviceFilter').value,
                    data_type: dataType,
                    page: page
                };
                
                // Add sensor_type or actuator_type based on data type
                if (dataType === 'sensors') {
                    params.sensor_type = document.getElementById('sensorTypeFilter').value;
                } else {
                    const actuatorType = document.getElementById('actuatorTypeFilter').value;
                    if (actuatorType) {
                        params.actuator_type = actuatorType;
                    }
                }
                
                params.start_date = document.getElementById('startDate').value;
                params.end_date = document.getElementById('endDate').value;

                fetchData(params);
            }

            function fetchData(params) {
                const loadingIndicator = document.getElementById('loadingIndicator');
                loadingIndicator.classList.remove('hidden');

                const queryString = new URLSearchParams(params).toString();
                
                // Fetch paginated data for table
                fetch(`{{ route('history.data') }}?${queryString}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingIndicator.classList.add('hidden');
                        
                        if (data.success) {
                            updateTable(data.data);
                            updatePagination(data.pagination);
                            
                            if (data.stats) {
                                updateStats(data.stats);
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        loadingIndicator.classList.add('hidden');
                        console.error('Error:', error);
                        alert('Failed to fetch data');
                    });

                // Fetch ALL data for chart (no pagination)
                const chartParams = {...params};
                delete chartParams.page;
                chartParams.per_page = 100000;
                const chartQueryString = new URLSearchParams(chartParams).toString();
                
                fetch(`{{ route('history.data') }}?${chartQueryString}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            updateChart(data.data);
                        }
                    })
                    .catch(error => {
                        console.error('Chart Error:', error);
                    });
            }

            function updateTable(data) {
                const tbody = document.getElementById('dataTableBody');
                const dataType = document.getElementById('dataTypeFilter').value;
                
                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-8 text-center">
                                <p class="text-gray-400">No data found</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.map(record => {
                    // Check if this is actuator data
                    if (record.actuator_type) {
                        return `
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-3 px-2">
                                    <span class="text-sm text-white">${record.device?.name || 'N/A'}</span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-100 rounded-lg text-xs font-medium capitalize">
                                        ${record.actuator_type}
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-lg font-semibold text-white">${parseFloat(record.value).toFixed(1)}</span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-sm text-indigo-200">${record.unit || '%'}</span>
                                </td>
                                <td class="py-3 px-2">
                                    ${record.status ? `<span class="px-2 py-1 rounded text-xs font-semibold ${record.status === 'ON' ? 'bg-green-500/20 text-green-100' : 'bg-gray-500/20 text-gray-100'}">${record.status}</span>` : ''}
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-sm text-indigo-200">${formatDateTime(record.created_at)}</span>
                                </td>
                            </tr>
                        `;
                    } else {
                        // Sensor data
                        return `
                            <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                                <td class="py-3 px-2">
                                    <span class="text-sm text-white">${record.device?.name || 'N/A'}</span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-100 rounded-lg text-xs font-medium capitalize">
                                        ${record.sensor_type ? record.sensor_type.replace('_', ' ') : 'Sensor'}
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-lg font-semibold text-white">${parseFloat(record.value).toFixed(2)}</span>
                                </td>
                                <td class="py-3 px-2">
                                    <span class="text-sm text-indigo-200">${record.unit || '-'}</span>
                                </td>
                                <td class="py-3 px-2"></td>
                                <td class="py-3 px-2">
                                    <span class="text-sm text-indigo-200">${formatDateTime(record.created_at)}</span>
                                </td>
                            </tr>
                        `;
                    }
                }).join('');
            }

            function updateStats(stats) {
                document.getElementById('statsSection').classList.remove('hidden');
                document.getElementById('statAvg').textContent = stats.avg !== null ? stats.avg.toFixed(2) : 'N/A';
                document.getElementById('statMin').textContent = stats.min !== null ? stats.min.toFixed(2) : 'N/A';
                document.getElementById('statMax').textContent = stats.max !== null ? stats.max.toFixed(2) : 'N/A';
                document.getElementById('statCount').textContent = stats.count || 0;
            }

            function updateChart(data) {
                if (data.length === 0) return;

                document.getElementById('chartSection').classList.remove('hidden');

                const labels = data.map(r => new Date(r.created_at)).reverse();
                const values = data.map(r => parseFloat(r.value)).reverse();
                
                // Get date range for chart title
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const dateRangeText = startDate && endDate 
                    ? ` (${formatDateShort(startDate)} - ${formatDateShort(endDate)})`
                    : '';
                
                // Get sensor info
                const sensorType = data[0]?.sensor_type?.replace('_', ' ').toUpperCase() || 'SENSOR';
                const unit = data[0]?.unit || '';

                const ctx = document.getElementById('trendChart');
                
                if (chart) {
                    chart.destroy();
                }

                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: `${sensorType} ${unit ? `(${unit})` : ''}`,
                            data: values,
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.2)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: `${sensorType} Trend${dateRangeText}`,
                                color: 'rgb(199, 210, 254)',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                },
                                padding: {
                                    bottom: 20
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: 'rgb(199, 210, 254)',
                                    font: {
                                        size: 12
                                    },
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 27, 75, 0.95)',
                                titleColor: 'rgb(199, 210, 254)',
                                bodyColor: 'rgb(255, 255, 255)',
                                borderColor: 'rgb(99, 102, 241)',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                callbacks: {
                                    title: function(context) {
                                        const date = new Date(context[0].parsed.x);
                                        return date.toLocaleString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        });
                                    },
                                    label: function(context) {
                                        return ` ${sensorType}: ${context.parsed.y.toFixed(2)} ${unit}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: unit ? `Value (${unit})` : 'Value',
                                    color: 'rgb(199, 210, 254)',
                                    font: {
                                        size: 13,
                                        weight: 'bold'
                                    }
                                },
                                ticks: { 
                                    color: 'rgb(199, 210, 254)',
                                    font: {
                                        size: 11
                                    },
                                    callback: function(value) {
                                        return value.toFixed(1);
                                    }
                                },
                                grid: { 
                                    color: 'rgba(255, 255, 255, 0.1)',
                                    drawBorder: false
                                }
                            },
                            x: {
                                type: 'time',
                                min: startDate ? new Date(startDate).setHours(0,0,0,0) : undefined,
                                max: endDate ? new Date(endDate).setHours(23,59,59,999) : undefined,
                                time: {
                                    unit: 'hour',
                                    displayFormats: {
                                        hour: 'MMM d, HH:mm',
                                        day: 'MMM d'
                                    },
                                    tooltipFormat: 'MMM d, yyyy HH:mm'
                                },
                                title: {
                                    display: true,
                                    text: 'Time',
                                    color: 'rgb(199, 210, 254)',
                                    font: {
                                        size: 13,
                                        weight: 'bold'
                                    }
                                },
                                ticks: {
                                    color: 'rgb(199, 210, 254)',
                                    font: {
                                        size: 10
                                    },
                                    maxRotation: 45,
                                    minRotation: 30,
                                    autoSkip: true,
                                    maxTicksLimit: 10
                                },
                                grid: { 
                                    color: 'rgba(255, 255, 255, 0.05)',
                                    drawBorder: false
                                }
                            }
                        }
                    }
                });
            }

            function formatDateShort(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }


            function updatePagination(pagination) {
                if (pagination.total === 0) {
                    document.getElementById('paginationSection').classList.add('hidden');
                    return;
                }

                document.getElementById('paginationSection').classList.remove('hidden');
                
                const pageInfo = document.getElementById('pageInfo');
                pageInfo.textContent = `Showing page ${pagination.current_page} of ${pagination.last_page} (${pagination.total} total records)`;

                const buttons = document.getElementById('paginationButtons');
                buttons.innerHTML = '';

                if (pagination.current_page > 1) {
                    buttons.innerHTML += `<button onclick="applyFilters(${pagination.current_page - 1})" class="px-3 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">Previous</button>`;
                }

                if (pagination.current_page < pagination.last_page) {
                    buttons.innerHTML += `<button onclick="applyFilters(${pagination.current_page + 1})" class="px-3 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/50 text-indigo-100 rounded-lg transition-all duration-300 font-medium">Next</button>`;
                }
            }

            function formatDateTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        </script>
    </x-slot>

</x-layout>
