--[[
    LuCI - Lua Configuration Interface
    License : (GPL-2.0) GNU General Public License Version 2
    Created & developed by @Taufik <https://t.me/taufik_n_a>
    © 2025 AlphaNetwork All rights reserved.
]]--

module("luci.controller.acmt", package.seeall)

local fs = require "nixio.fs"
local sys = require "luci.sys"
local util = require "luci.util"
local http = require "luci.http"
local json = require "luci.jsonc"
local nixio = require "nixio"
local uci = require "luci.model.uci".cursor()

function index()
    if not fs.access("/etc/config/acmt") then
        return
    end

    if not fs.access("/etc/config/uhttpd") then
        return
    end

    -- entry({"admin", "services", "acmt", "rpb_action"}, call("rpb_action")).leaf = true
    entry({"admin", "services", "acmt", "rpb_status"}, call("rpb_status")).leaf = true
    entry({"admin", "services", "acmt", "rpb_active_ports"}, call("rpb_active_ports")).leaf = true
    entry({"admin", "services", "acmt", "rpb_active_radacct"}, call("rpb_active_radacct"), nil).leaf = true
    entry({"admin", "services", "acmt", "rpb_compare_hash"}, call("rpb_compare_hash")).leaf = true
    entry({"admin", "services", "acmt", "rpb_save_hash"}, call("rpb_save_hash")).leaf = true
    entry({"admin", "services", "acmt", "rpb_del_hash"}, call("rpb_del_hash")).leaf = true
    entry({"admin", "services", "acmt", "rpb_log_data"}, call("rpb_log_data")).leaf = true
    entry({"admin", "services", "acmt", "rpb_reset_log"}, call("rpb_reset_log")).leaf = true
    entry({"admin", "services", "acmt", "rpb_start"}, call("rpb_start")).leaf = true
    entry({"admin", "services", "acmt", "rpb_stop"}, call("rpb_stop")).leaf = true
    entry({"admin", "services", "acmt", "rpb_restart"}, call("rpb_restart")).leaf = true
    entry({"admin", "services", "acmt", "uhttpd_status"}, call("uhttpd_status")).leaf = true
    entry({"admin", "services", "acmt", "uhttpd_log_data"}, call("uhttpd_log_data")).leaf = true
    entry({"admin", "services", "acmt", "uhttpd_restart"}, call("uhttpd_restart")).leaf = true
end

--
-- For Router Port Blocker (RPB)
--
function rpb_status()
    local app_pid_en = true -- true or false
    local ctrl_pid_en = false -- true or false
    local hash_en = false -- true or false

    local status = {}
    local app_pid_num = nil
    local ctrl_pid_num = nil
    local nft_hash_list = {}

    local app = util.exec("ps w | grep '[a]cmt start' | grep -v 'acmt-ctrl' | awk '{print $1}' | head -n1")
    local ctrl = util.exec("ps w | grep '[a]cmt-ctrl up' | awk '{print $1}' | head -n1")
    local nft = os.execute("nft list chain inet acmt input >/dev/null 2>&1")

    local pid_file = "/tmp/run/acmt/acmt.pid"
    local ctrl_pid_file = "/tmp/run/acmt/acmt_ctrl.pid"

    if fs.access(pid_file) then
        app_num = util.exec("cat " .. pid_file)
    else
        app_num = app
    end

    if fs.access(ctrl_pid_file) then
        ctrl_num = util.exec("cat " .. ctrl_pid_file)
    else
        ctrl_num = ctrl
    end

    app_pid_num = app_num:gsub("%s+", "")
    ctrl_pid_num = ctrl_num:gsub("%s+", "")

    if hash_en then
        -- uci_hash = util.exec("uci show acmt | md5sum | awk '{print $1}'"):gsub("\n", "")
        uci_hash = util.exec("[ -f /tmp/run/acmt/acmt_uci.hash ] && cat /tmp/run/acmt/acmt_uci.hash || echo ''"):gsub("\n", "")

        local files = util.exec("ls /tmp/run/acmt/acmt_*_*.hash 2>/dev/null")
        for file in files:gmatch("[^\n]+") do
            local hash = util.exec("cat " .. file .. " 2>/dev/null"):gsub("\n", "")
            if hash ~= "" then
                table.insert(nft_hash_list, hash)
            end
        end
    end

    if app_pid_num ~= "" and os.execute("kill -0 " .. app_pid_num .. " >/dev/null 2>&1") == 0 and nft == 0 then
        status.running = true
        status.method = util.exec("ps | grep '[a]cmt'") .. "nft: " .. (nft == 0 and "running" or "not_running")
        status.uci_hash = uci_hash
        status.nft_hash = nft_hash_list

        if app_pid_en then
            status.app_pid = "(PID: " .. app_pid_num .. ")"
        end

        if ctrl_pid_en then
            status.ctrl_pid = "(CTRL: " .. ctrl_pid_num .. ")"
        end
    elseif app_pid_num ~= "" and os.execute("kill -0 " .. app_pid_num .. " >/dev/null 2>&1") == 0 and nft == 1 then
        status.running = true
        status.method = util.exec("ps | grep '[a]cmt'") .. "nft: " .. (nft == 0 and "running" or "not_running")
        status.uci_hash = uci_hash
        status.nft_hash = nft_hash_list

        if app_pid_en then
            status.app_pid = "(⚠️ NFT NOT FOUND) (PID: " .. app_pid_num .. ")"
        end

        if ctrl_pid_en then
            status.ctrl_pid = "(CTRL: " .. ctrl_pid_num .. ")"
        end
    elseif nft == 0 then
        status.running = false
        status.method = "NFT is running, deleting NFT"
        os.execute("nft delete table inet acmt >/dev/null 2>&1")
    else
        status.running = false
        status.method = "not_running"
    end

    http.prepare_content("application/json")
    http.write(json.stringify(status))
end

function rpb_active_ports()
    local complete_en = false -- true or false
    local ports = {}
    local cmd = nil

    if complete_en then
        cmd = io.popen("/bin/netstat -tuln 2>/dev/null | tail -n +3 | awk '{print $4}' | awk -F':' '{print $NF}' | sort -u")
    else
        cmd = io.popen("netstat -lnt 2>/dev/null | awk 'NR>2 {print $4}' | awk -F: '{print $NF}' | sort -u")
    end

    if cmd then
        for port in cmd:lines() do
            if tonumber(port) then
                table.insert(ports, port)
            end
        end
        cmd:close()
    end

    luci.http.prepare_content("application/json")
    luci.http.write_json({ result = ports })
end

function rpb_active_radacct()
	local db_host = uci:get("acmt", "main", "db_server") or "127.0.0.1"
	local db_user = uci:get("acmt", "main", "db_user") or "root"
	local db_pass = uci:get("acmt", "main", "db_pass") or ""
	local db_name = uci:get("acmt", "main", "db_name") or "radius"
	local db_port = uci:get("acmt", "main", "mysql_port") or "3306"

	local cmd = string.format(
		"mysql -h%s -P%s -u%s -p%s -N -e 'SELECT framedipaddress, callingstationid FROM %s.radacct WHERE framedipaddress IS NOT NULL AND framedipaddress <> \"\" AND (acctstoptime IS NULL OR acctstoptime=\"\");'",
		db_host, db_port, db_user, db_pass, db_name
	)

	local ipset = {}
	local macset = {}

	local pipe = io.popen(cmd)
	if pipe then
		for line in pipe:lines() do
			local ip, mac = line:match("^(%S+)%s+(%S+)$")
			if ip and mac then
				table.insert(ipset, ip)
				table.insert(macset, mac:upper())
			end
		end
		pipe:close()
	end

	http.prepare_content("application/json")
	http.write_json({
		iplist = table.concat(ipset, ","),
		maclist = table.concat(macset, ",")
	})
end

--[[ Lanjutan untuk versi selanjutnya ( masih dalam pengembangan! )
function action_acmt(section, command)
    local http = require "luci.http"
    local util = require "luci.util"

    local s = http.formvalue("section")
    local c = http.formvalue("cmd")

    if s and c then
        local cmd = string.format("/etc/init.d/acmt %s %s", c, s)
        local output = util.exec(cmd .. " 2>&1")
        http.prepare_content("application/json")
        http.write_json({ status = "ok", output = output })
    else
        http.prepare_content("application/json")
        http.write_json({ status = "error", message = "Missing section or cmd" })
    end
end
]]--

function rpb_compare_hash()
    local file_hash = util.exec("[ -f /tmp/run/acmt/acmt_uci.hash ] && cat /tmp/run/acmt/acmt_uci.hash || echo ''"):gsub("\n", "")
    local uci_hash = util.exec("uci show acmt | md5sum | cut -d ' ' -f1"):gsub("\n", "")

    local response = {
        file_hash = file_hash,
        uci_hash = uci_hash,
        changed = (file_hash ~= uci_hash)
    }

    http.prepare_content("application/json")
    http.write(json.stringify(response))
end

--[[
function rpb_save_hash()
    local uci_hash = util.exec("uci show acmt | md5sum | cut -d ' ' -f1"):gsub("\n", "")
    util.exec("echo '" .. uci_hash .. "' > /tmp/run/acmt/acmt_uci.prev")
    http.status(200, "OK")
end

function rpb_del_hash()
    util.exec("[ -f /tmp/run/acmt/acmt_uci.prev ] && rm -f /tmp/run/acmt/acmt_uci.prev")
    util.exec("[ -f /tmp/run/acmt/acmt.conf ] && rm -f /tmp/run/acmt/acmt.conf")
    http.status(200, "OK")
end
]]--

function rpb_log_data()
    local log_file = "/tmp/log/acmt.log"
    local content = "Log file not found."

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

    http.prepare_content("text/plain")
    http.write(content)
end

function rpb_reset_log()
    local log_file = "/tmp/log/acmt.log"
    -- util.exec("[ -f " .. log_file .. " ] && rm -f " .. log_file)
    sys.call("echo -n > " .. log_file)
    http.status(200, "OK")
    http.write("Log cleared")
end

function rpb_start()
    sys.init.start("access-management")
    http.status(200, "OK")
end

function rpb_stop()
    sys.init.stop("access-management")
    http.status(200, "OK")
end

function rpb_restart()
    sys.init.restart("access-management")
    http.status(200, "OK")
end
----- RPB END -----

--
-- For uHTTPd Web Server
--
local function get_pid_by_port(port)
    local cmd = string.format("netstat -tlnp 2>/dev/null | grep ':%s ' | awk '{print $7}' | cut -d'/' -f1", port)
    return sys.exec(cmd):match("(%d+)")
end

function uhttpd_status()
    local status = {}
    local pid_strs = {}

    uci:foreach("uhttpd", "uhttpd", function(s)
        local name = s['.name']
        local listen_ports = s.listen_http or {}

        if type(listen_ports) == "string" then
            listen_ports = { listen_ports }
        end

        local pid_found
        for _, addr in ipairs(listen_ports) do
            local port = addr:match(":(%d+)")
            if port then
                local pid = get_pid_by_port(port)
                if pid then
                    pid_found = pid
                    break
                end
            end
        end

        if pid_found then
            table.insert(pid_strs, "(" .. name .. "_PID: " .. pid_found .. ")")
        end
    end)

    if #pid_strs > 0 then
        status.running = true
        status.pid = table.concat(pid_strs, " ")
    else
        status.running = false
        status.pid = ""
    end

    http.prepare_content("application/json")
    http.write(json.stringify(status))
end

--[[
function uhttpd_status()
    local status = {}
    local raw_pids = sys.exec("pidof uhttpd"):gsub("\n", "")
    local pid_list = {}

    for pid in raw_pids:gmatch("%d+") do
        table.insert(pid_list, pid) -- urutan asli, bukan dibalik
    end

    -- Ambil semua nama unik di config uhttpd
    local uci_sections = {}
    uci:foreach("uhttpd", "uhttpd", function(s)
        table.insert(uci_sections, s['.name'])
    end)

    -- Cocokkan PID dengan nama section
    local pid_strs = {}
    for i, name in ipairs(uci_sections) do
        local pid = pid_list[i]
        if pid then
            table.insert(pid_strs, "(" .. name .. "_PID: " .. pid .. ")")
        end
    end

    if #pid_strs > 0 then
        status.running = true
        status.pid = table.concat(pid_strs, " ")
    else
        status.running = false
        status.pid = ""
    end

    http.prepare_content("application/json")
    http.write(json.stringify(status))
end
]]--

--[[
function uhttpd_status()
    local status = {}
    local raw_pids = sys.exec("pidof uhttpd"):gsub("\n", "")
    local pid_list = {}
    local main_pid, users_pid

    for pid in raw_pids:gmatch("%d+") do
        table.insert(pid_list, 1, pid)
    end

    if pid_list[2] and pid_list[2] ~= "" then
        main_pid = pid_list[2]
        users_pid = pid_list[1]
    elseif pid_list[1] and pid_list[1] ~= "" then
        main_pid = pid_list[1]
    end

    if main_pid and main_pid ~= "" and users_pid and users_pid ~= "" then
        status.running = true
        status.pid = "(MS_PID: " .. main_pid .. ") (US_PID: " .. users_pid .. ")"
    elseif main_pid and main_pid ~= "" then
        status.running = true
        status.pid = "(MS_PID: " .. main_pid .. ")"
    else
        status.running = false
        status.pid = ""
    end

    http.prepare_content("application/json")
    http.write(json.stringify(status))
end
]]--

function uhttpd_log_data()
    local cmd = "logread -e uhttpd"
    local handle = io.popen(cmd)
    if not handle then
        luci.http.status(500, "Failed to execute logread command")
        luci.http.prepare_content("text/plain")
        luci.http.write("")
        return
    end

    local result = handle:read("*a") or ""
    handle:close()

    local lines = {}
    -- Masukkan line ke lines dengan urutan terbalik (reverse)
    for line in result:gmatch("[^\r\n]+") do
        table.insert(lines, 1, line)
    end

    local content = table.concat(lines, "\n")

    luci.http.prepare_content("text/plain")
    luci.http.write(content)
end

--[[
function uhttpd_log_data()
    local cmd = "logread | grep uhttpd"
    local f = io.popen(cmd, "r")
    local content = ""

    if f then
        for line in f:lines() do
            table.insert(lines, 1, line)
        end
        f:close()

        content = table.concat(lines, "\n")
    end

    luci.http.prepare_content("text/plain")
    luci.http.write(content)
end

function uhttpd_log_data()
    local sys = require "luci.sys"
    local http = require "luci.http"

    -- Ambil log terkait uhttpd
    local cmd = sys.exec("logread | grep -i uhttpd")

    if cmd then
        local lines = {}
        for line in cmd:lines() do
            table.insert(lines, line)
        end
        cmd:close()

        local reversed = {}
        for i = #lines, 1, -1 do
            table.insert(reversed, lines[i])
        end

        content = table.concat(reversed, "\n")
    end

    http.prepare_content("text/plain")
    http.write(content or "")
end
]]--

function uhttpd_restart()
    sys.init.restart("uhttpd")
    http.status(200, "OK")
end
----- uHTTPd END -----