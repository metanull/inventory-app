Jekyll::Hooks.register :site, :post_write do |site|
  # Copy api.json from _openapi to _site root
  api_json_source = File.join(site.source, '_openapi', 'api.json')
  api_json_dest = File.join(site.dest, 'api.json')
  
  if File.exist?(api_json_source)
    FileUtils.cp(api_json_source, api_json_dest)
    puts "Copied api.json to site root"
  end
  
  # Copy swagger-ui.html from _openapi to _site root
  swagger_source = File.join(site.source, '_openapi', 'swagger-ui.html')
  swagger_dest = File.join(site.dest, 'swagger-ui.html')
  
  if File.exist?(swagger_source)
    FileUtils.cp(swagger_source, swagger_dest)
    puts "Copied swagger-ui.html to site root"
  end
end
