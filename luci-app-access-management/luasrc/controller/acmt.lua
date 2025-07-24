module("luci.controller.acmt", package.seeall)

function index()
    entry({"admin", "services", "acmt", "rpb_status"}, call("rpb_status")).leaf = true
    entry({"admin", "services", "acmt", "rpb_log_data"}, call("rpb_log_data")).leaf = true
    entry({"admin", "services", "acmt", "rpb_reset_log"}, call("rpb_reset_log")).leaf = true
    entry({"admin", "services", "acmt", "rpb_start"}, call("rpb_start")).leaf = true
    entry({"admin", "services", "acmt", "rpb_stop"}, call("rpb_stop")).leaf = true
    entry({"admin", "services", "acmt", "rpb_restart"}, call("rpb_restart")).leaf = true
    entry({"admin", "services", "acmt", "uhttpd_status"}, call("uhttpd_status")).leaf = true
    entry({"admin", "services", "acmt", "uhttpd_restart"}, call("uhttpd_restart")).leaf = true
end

local util = require "luci.util"
local fs = require "nixio.fs"
local http = require "luci.http"
local sys = require "luci.sys"
local json = require "luci.jsonc"

--
-- For Router Port Blocker (RPB)
--
function rpb_status()
    local app_pid_en = true
    local ctrl_pid_en = false
    local hash_en = false

    local status = {}
    local app_pid_num = nil
    local ctrl_pid_num = nil
    local nft_hash_list = {}

    local app = util.exec("ps | grep '[a]cmt start' | awk '{print $1}'")
    local ctrl = util.exec("ps | grep '[a]cmt-ctrl up' | awk '{print $1}'")
    local nft = os.execute("nft list chain inet acmt input >/dev/null 2>&1")

    local pid_file = "/tmp/run/acmt.pid"

    if fs.access(pid_file) then
        app_num = util.exec("cat " .. pid_file)
    else
        app_num = app
    end

    local ctrl_pid_file = "/tmp/run/acmt_ctrl.pid"

    if fs.access(ctrl_pid_file) then
        ctrl_num = util.exec("cat " .. ctrl_pid_file)
    else
        ctrl_num = ctrl
    end

    app_pid_num = app_num:gsub("%s+", "")
    ctrl_pid_num = ctrl_num:gsub("%s+", "")

    if hash_en then
        uci_hash = util.exec("[ -f /tmp/run/acmt_uci.hash ] && cat /tmp/run/acmt_uci.hash || echo ''"):gsub("\n", "")

        local files = util.exec("ls /tmp/run/acmt_*_*.hash 2>/dev/null")
        for file in files:gmatch("[^\n]+") do
            local hash = util.exec("cat " .. file .. " 2>/dev/null"):gsub("\n", "")
            if hash ~= "" then
                table.insert(nft_hash_list, hash)
            end
        end
    end

    if app_pid_num ~= "" and app_pid_num:match("^%d+$") and nft == 0 then
        status.running = true
        if app_pid_en then
            status.app_pid = "(PID: " .. app_pid_num .. ")"
        end
        if ctrl_pid_en then
            status.ctrl_pid = "(CTRL: " .. ctrl_pid_num .. ")"
        end
        status.method = util.exec("ps | grep '[a]cmt'") .. "nft: " .. (nft == 0 and "running" or "not_running")
        status.uci_hash = uci_hash
        status.nft_hash = nft_hash_list 
    elseif nft == 0 then
        status.running = true
        status.method = (nft == 0 and "running" or "not_running")
        status.uci_hash = uci_hash
        status.nft_hash = nft_hash_list 
    else
        status.running = false
        status.method = "not_running"
    end

    http.prepare_content("application/json")
    http.write(json.stringify(status))
end

function rpb_log_data()
    local log_file = "/tmp/log/acmt.log"
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

    http.prepare_content("text/plain")
    http.write(content)
end

function rpb_reset_log()
    local log_file = "/tmp/log/acmt.log"
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

function uhttpd_restart()
    sys.init.restart("uhttpd")
    http.status(200, "OK")
end
----- uHTTPd END -----