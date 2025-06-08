-----
--- uHTTPd Web Server
-----
--- Main uHTTPd Web Server & Users
-----

m = Map("uhttpd", translate("uHTTPd Web Server"), translate("Main uHTTPd Web Server & Users"))

--
-- Status
--
d = luci.dispatcher.build_url("admin", "services", "access-management", "uhttpd_status")
s = m:section(SimpleSection, "Status", "")
o = s:option(DummyValue, "status", "")
o.rawhtml = true
o.value = [[
<div id="uhttpd-status"><i>Memuat status...</i></div>
<script>
    function updateStatus() {
        fetch("]] .. d .. [[")
        .then(response => response.json())
        .then(data => {
            const statusDiv = document.getElementById("uhttpd-status");
            if (data.running) {
                statusDiv.innerHTML = '<p><strong><span style="color:green;"><i>uHTTPd RUNNING</i></span></strong></p>';
            } else {
                statusDiv.innerHTML = '<p><strong><span style="color:red;"><i>uHTTPd NOT RUNNING</i></span></strong></p>';
            }
        })
        .catch(err => {
            document.getElementById("uhttpd-status").innerHTML = '<p><strong><span style="color:gray;"><i>Status tidak dapat diambil</i></span></strong></p>';
        });
    }

    updateStatus();
    setInterval(updateStatus, 1000);
</script>
]]

--
-- Section "main"
--
s_main = m:section(NamedSection, "main", "uhttpd", "Main Server")
s_main.addremove = false
s_main.anonymous = false

s_main:tab("general", "General")
s_main:tab("listen", "Listen")
s_main:tab("index", "Index")

--
-- Tab: General
--
s_main:taboption("general", Value, "home", "Home Directory")
s_main:taboption("general", Value, "max_requests", "Max Requests")
s_main:taboption("general", Value, "max_connections", "Max Connections")

--
-- Tab: Listen
--
s_main:taboption("listen", DynamicList, "listen_http", "Listen HTTP")
s_main:taboption("listen", DynamicList, "listen_https", "Listen HTTPS")

--
-- Tab: Index
--
s_main:taboption("index", DynamicList, "index_page", "Index Pages")

--
-- Section "users"
--
s_users = m:section(NamedSection, "users", "uhttpd", "Users Server")
s_users.addremove = false
s_users.anonymous = false

s_users:tab("general", "General")
s_users:tab("listen", "Listen")
s_users:tab("index", "Index")

--
-- Tab: General
--
s_users:taboption("general", Value, "home", "Home Directory")
s_users:taboption("general", Value, "max_requests", "Max Requests")

--
-- Tab: Listen
--
s_users:taboption("listen", DynamicList, "listen_http", "Listen HTTP")
s_users:taboption("listen", DynamicList, "listen_https", "Listen HTTPS")

--
-- Tab: Index
--
s_users:taboption("index", DynamicList, "index_page", "Index Pages")

--
-- Apply
--
apply = luci.http.formvalue("cbi.apply")
if apply then
    luci.sys.call("(sleep 6 && /etc/init.d/uhttpd restart) &")
end

return m
----- END -----