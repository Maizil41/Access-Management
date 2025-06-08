-----
--- Router Port Blocker
-----
--- Hotspot & PPPoE User Blocker to RadMonv2 Gateway Router Port
-----

m = Map("access-management", translate("Router Port Blocker"), translate("Hotspot & PPPoE Users Blocker to RadMonv2 Gateway Router Port"))

--
-- Status
--
d = luci.dispatcher.build_url("admin", "services", "access-management", "rpb_status")
s = m:section(SimpleSection, "Status", "")
o = s:option(DummyValue, "status", "")
o.rawhtml = true
o.value = [[
<div id="rpb-status"><i>Memuat status...</i></div>
<script>
    function updateStatus() {
        fetch("]] .. d .. [[")
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById("rpb-status");
            if (data.running) {
                statusDiv.innerHTML = '<p><strong><span style="color:green;"><i>Router Port Blocker RUNNING</i></span></strong></p>';
            } else {
                statusDiv.innerHTML = '<p><strong><span style="color:red;"><i>Router Port Blocker NOT RUNNING</i></span></strong></p>';
            }
        })
        .catch(err => {
            document.getElementById("rpb-status").innerHTML = '<p><strong><span style="color:gray;"><i>Status tidak dapat diambil</i></span></strong></p>';
        });
    }

    updateStatus();
    setInterval(updateStatus, 1000);
</script>
]]

--
-- Main Selection
--
s = m:section(TypedSection, "access_management", translate("Main Settings"))
s.addremove = false
s.anonymous = true

s:tab("settings", translate("Settings"))
s:tab("logs", translate("Logs"))

--
-- Tab: Settings
--
o = s:taboption("settings", Flag, "enabled", translate("Enabled"), translate(""))
o.default = 0
o.optional = false
o = s:taboption("settings", Value, "ports", translate("Port Black List"), translate("Masukan port black list yang tidak ingin diakses oleh users, Contoh: 22,8080,8443"))
o.default = ""
o = s:taboption("settings", DynamicList, "whitelist", translate("IP White List"), translate("Masukan IP address white list / IP pengecualian router port blocker (optional)"))
o.default = ""

--
-- Tab: Logs
--
d = luci.dispatcher.build_url("admin", "services", "access-management", "rpb_log_data")
o = s:taboption("logs", DummyValue, "log_output", "")
o.rawhtml = true
o.value = [[
<style>
    .log-output {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;
        background-color: #f9f9f9;
        white-space: pre-wrap;
    }
</style>
<div id="rpb-log" class="log-output"></div>
<script>
    function updateLog() {
        fetch("]] .. d .. [[")
        .then(response => response.text())
        .then(data => {
            const logDiv = document.getElementById("rpb-log");
            logDiv.innerText = data;
        })
        .catch(error => {
            document.getElementById("rpb-log").innerHTML = '<p><strong><span style="color:gray;"><i>Status tidak dapat diambil</i></span></strong></p>';
        });
    }

    updateLog();
    setInterval(updateLog, 1000);
</script>
]]

d = luci.dispatcher.build_url("admin", "services", "access-management", "rpb_clear_log")
r = s:taboption("logs", DummyValue, "open_link", "")
r.rawhtml = true
r.value = [[
<div style="display: flex; gap: 10px;">
    <button onclick="resetLog()" style="padding: 8px 15px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Hapus Log</button>
</div>
<script>
    function resetLog() {
        fetch("]] .. d .. [[", {
            method: "POST"
        })
        .then(response => {
            if (response.ok) {
                updateLog();
            }
        });
    }
</script>
]]

--
-- Apply
--
apply = luci.http.formvalue("cbi.apply")
if apply then
    luci.sys.call("(sleep 6 && /usr/bin/access-management restart) &")
end

return m
----- END -----