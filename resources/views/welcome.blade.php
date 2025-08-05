<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Health</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            background-color: #f7fafc;
            color: #1a202c;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        .health-status {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        .healthy {
            background-color: #c6f6d5;
            color: #22543d;
        }
        .unhealthy {
            background-color: #fed7d7;
            color: #742a2a;
        }
        .warning {
            background-color: #feebc8;
            color: #7b341e;
        }
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .badge.healthy {
            background-color: #38a169; /* green */
        }
        .badge.unhealthy {
            background-color: #e53e3e; /* red */
        }
        .badge.warning {
            background-color: #dd6b20; /* orange */
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Application Health Status</h1>
        <div id="health-container">
            <p>Loading health status...</p>
        </div>
    </div>

    <script>
        async function fetchHealth() {
            const container = document.getElementById('health-container');
            try {
                const response = await fetch('/api/health');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                let overallStatusClass = 'healthy';
                if (data.status === 'unhealthy') {
                    overallStatusClass = 'unhealthy';
                } else if (data.status === 'warning') {
                    overallStatusClass = 'warning';
                }

                let checksHtml = '<div style="background-color: #fff; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);">';
                for (const [key, value] of Object.entries(data.checks)) {
                    const statusClass = value.status || 'healthy';
                    checksHtml += `
                        <div class="check-item">
                            <span style="font-weight: 500;">${key.charAt(0).toUpperCase() + key.slice(1)}</span>
                            <span class="badge ${statusClass}">${value.message}</span>
                        </div>
                    `;
                }
                checksHtml += '</div>';

                container.innerHTML = `
                    <div class="health-status ${overallStatusClass}" style="margin-bottom: 1.5rem;">
                        <p><strong>Overall Status:</strong> <span style="text-transform: capitalize;">${data.status}</span></p>
                        <p><strong>Timestamp:</strong> ${new Date(data.timestamp).toLocaleString()}</p>
                    </div>
                    ${checksHtml}
                `;
            } catch (error) {
                console.error('Failed to fetch health status:', error);
                container.innerHTML = `<div class="health-status unhealthy">Failed to fetch health status. Please check the console for more details.</div>`;
            }
        }

        document.addEventListener('DOMContentLoaded', fetchHealth);
    </script>
</body>
</html>
