s = SimpleForm("about", "Access Management")
s.reset = false
s.submit = false
s.template = nil

function s.render(self, ...)
    luci.http.prepare_content("text/html")
    luci.http.write([[
  <div class="cbi-map">
    <div class="cbi-section">
      <div id="app-container"></div>
    </div>
  </div>
  <script src="/luci-static/resources/access_management_about.js"></script>
    ]])
end

return s