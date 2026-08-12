@if(session()->has('sap_debug_logs') && count(session('sap_debug_logs')) > 0)
    <script>
        (function() {
            try {
                const debugLogs = @json(session('sap_debug_logs'));
                if (Array.isArray(debugLogs) && debugLogs.length > 0) {
                    console.group('%c 🚀 SAP B1 Service Layer Debug Logs', 'color: #6366f1; font-weight: bold; font-size: 14px;');
                    debugLogs.forEach((log, index) => {
                        console.groupCollapsed(
                            `%c [${log.method}] %c ${log.url} %c (DB: ${log.database || 'N/A'}) - Status ${log.status}`,
                            log.status >= 200 && log.status < 300 ? 'color: #22c55e; font-weight: bold;' : 'color: #ef4444; font-weight: bold;',
                            'color: #38bdf8; font-weight: bold;',
                            'color: #f59e0b; font-weight: bold;'
                        );
                        console.info('%c 📍 Target URL Link:', 'color: #3b82f6; font-weight: bold;', log.url);
                        console.info('%c ⚙️ HTTP Method:', 'color: #a855f7; font-weight: bold;', log.method);
                        console.info('%c 🗄️ Target Database:', 'color: #f59e0b; font-weight: bold;', log.database || 'N/A');
                        console.info('%c 🚥 HTTP Status Code:', 'color: #10b981; font-weight: bold;', log.status);
                        if (log.body) {
                            console.info('%c 📦 Request Payload (Body):', 'color: #ec4899; font-weight: bold;');
                            console.log(log.body);
                        }
                        console.info('%c 📥 Response Payload:', 'color: #06b6d4; font-weight: bold;');
                        console.log(log.response);
                        console.groupEnd();
                    });
                    console.groupEnd();
                }
            } catch (err) {
                console.error('Debug log parser error:', err);
            }
        })();
    </script>
    @php
        session()->forget('sap_debug_logs');
    @endphp
@endif
