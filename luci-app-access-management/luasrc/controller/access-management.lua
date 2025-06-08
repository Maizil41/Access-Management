module("luci.controller.access-management", package.seeall)

function index()
    entry({"admin", "services", "access-management"}, firstchild(), "Access Management", 80).dependent = false
    entry({"admin", "services", "access-management", "rpb"}, cbi("access-management/rpb"), "Router Port Blocker", 1).leaf = true
    entry({"admin", "services", "access-management", "uhttpd"}, cbi("access-management/uhttpd"), "uHTTPd Web Server", 2).leaf = true
    entry({"admin", "services", "access-management", "about"}, cbi("access-management/about"), "About", 3).leaf = true
    entry({"admin", "services", "access-management", "rpb_status"}, call("rpb_status")).leaf = true
    entry({"admin", "services", "access-management", "rpb_log_data"}, call("rpb_log")).leaf = true
    entry({"admin", "services", "access-management", "rpb_clear_log"}, call("rpb_clear")).leaf = true
    entry({"admin", "services", "access-management", "uhttpd_status"}, call("uhttpd_status")).leaf = true
end

--
-- For Router Port Blocker (RPB)
--
function rpb_status()
    local status = {}
    status.running = os.execute("/usr/bin/access-management status >/dev/null 2>&1") == 0
    luci.http.prepare_content("application/json")
    luci.http.write_json(status)
end

function rpb_log()
    local log_file = "/var/log/access_management.log"
    local content = ""

    local f = io.open(log_file, "r")
    if f then
        local lines = {}
        for line in f:lines() do
            table.insert(lines, line)
        end
        f:close()

        local reversed = {}
        for i = #lines, 1, -1 do
            table.insert(reversed, lines[i])
        end

        content = table.concat(reversed, "\n")
    end

    luci.http.prepare_content("text/plain")
    luci.http.write(content)
end

function rpb_clear()
    local log_file = "/var/log/access_management.log"

    luci.sys.call("echo -n > " .. log_file)
    luci.http.status(200, "OK")
    luci.http.write("Log cleared")
end
----- RPB END -----

--
-- For uHTTPd Web Server
--
function uhttpd_status()
    local status = {}
    status.running = os.execute("/etc/init.d/uhttpd status >/dev/null 2>&1") == 0
    luci.http.prepare_content("application/json")
    luci.http.write_json(status)
end
----- uHTTPd END -----