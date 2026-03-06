# Asset Versioning and Cache Busting Setup

## Overview

The asset helper configuration enables cache busting and versioning for static assets (CSS, JavaScript, images, etc.). This ensures browsers load the latest version of your assets when they change.

## Configuration

### Location
Asset configuration is defined in `src/Neutrino/src/ConfigProvider.php` in the `getAssetConfig()` method.

### Current Setup

```php
public function getAssetConfig(): array
{
    return [
        'asset' => [
            'resource_map' => [
                // Sandbox theme assets
                'sandbox/assets/css/plugins.css' => 'sandbox/assets/css/plugins.css',
                'sandbox/assets/css/style.css'   => 'sandbox/assets/css/style.css',
                'sandbox/assets/js/plugins.js'   => 'sandbox/assets/js/plugins.js',
                'sandbox/assets/js/theme.js'     => 'sandbox/assets/js/theme.js',
                
                // Add versioned assets here when needed
                // Example with version hash:
                // 'css/style.css' => 'css/style-3a97ff4ee3.css',
                // 'js/vendor.js' => 'js/vendor-a507086eba.js',
            ],
        ],
    ];
}
```

## Usage in Templates

### Using the Asset Helper

In your templates, use the `asset()` helper instead of hardcoded paths:

```php
<!-- CSS -->
<link rel="stylesheet" href="<?= $this->asset('sandbox/assets/css/plugins.css') ?>">
<link rel="stylesheet" href="<?= $this->asset('sandbox/assets/css/style.css') ?>">

<!-- JavaScript -->
<script src="<?= $this->asset('sandbox/assets/js/plugins.js') ?>"></script>
<script src="<?= $this->asset('sandbox/assets/js/theme.js') ?>"></script>

<!-- Images -->
<img src="<?= $this->asset('sandbox/assets/img/logo.png') ?>" alt="Logo">
```

## How It Works

1. **Without Versioning**: The asset helper returns the path as-is with a leading slash:
   ```php
   $this->asset('sandbox/assets/css/style.css')
   // Returns: /sandbox/assets/css/style.css
   ```

2. **With Versioning**: The asset helper uses the resource_map to return the versioned path:
   ```php
   // Configuration:
   'css/style.css' => 'css/style-3a97ff4ee3.css',
   
   // In template:
   $this->asset('css/style.css')
   // Returns: /css/style-3a97ff4ee3.css
   ```

## Adding Versioned Assets

### Method 1: Manual Hash (Recommended for Production)

When you deploy, generate a hash based on file content and update the resource_map:

```php
'resource_map' => [
    'sandbox/assets/css/style.css' => 'sandbox/assets/css/style-3a97ff4ee3.css',
    'sandbox/assets/js/theme.js'   => 'sandbox/assets/js/theme-b8c9d1e2f3.js',
],
```

Then rename the actual files:
```bash
cp public/sandbox/assets/css/style.css public/sandbox/assets/css/style-3a97ff4ee3.css
cp public/sandbox/assets/js/theme.js public/sandbox/assets/js/theme-b8c9d1e2f3.js
```

### Method 2: Query String (Simple Alternative)

Alternatively, you can use query strings for cache busting:

```php
'resource_map' => [
    'sandbox/assets/css/style.css' => 'sandbox/assets/css/style.css?v=3a97ff4ee3',
],
```

## Build Script Example

Create a build script to automate asset versioning:

```php
#!/usr/bin/env php
<?php
// bin/build-assets.php

$assetsDir = __DIR__ . '/../public/sandbox/assets';
$configFile = __DIR__ . '/../src/Neutrino/src/ConfigProvider.php';

$assets = [
    'css/plugins.css',
    'css/style.css',
    'js/plugins.js',
    'js/theme.js',
];

$resourceMap = [];

foreach ($assets as $asset) {
    $filePath = $assetsDir . '/' . $asset;
    
    if (file_exists($filePath)) {
        $hash = substr(md5_file($filePath), 0, 10);
        $pathInfo = pathinfo($asset);
        $versionedAsset = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $hash . '.' . $pathInfo['extension'];
        
        $resourceMap["sandbox/assets/$asset"] = "sandbox/assets/$versionedAsset";
        
        // Copy file with versioned name
        copy($filePath, $assetsDir . '/' . $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '-' . $hash . '.' . $pathInfo['extension']);
        
        echo "Versioned: $asset -> $versionedAsset\n";
    }
}

// Output the resource map configuration
echo "\nAdd this to your ConfigProvider:\n\n";
echo "'resource_map' => [\n";
foreach ($resourceMap as $original => $versioned) {
    echo "    '$original' => '$versioned',\n";
}
echo "]\n";
```

## Environment-Specific Configuration

You can use different configurations for development and production:

```php
public function getAssetConfig(): array
{
    $isDevelopment = getenv('APP_ENV') === 'development';
    
    if ($isDevelopment) {
        // Development: No versioning for easier debugging
        return [
            'asset' => [
                'resource_map' => [
                    'sandbox/assets/css/plugins.css' => 'sandbox/assets/css/plugins.css',
                    'sandbox/assets/css/style.css'   => 'sandbox/assets/css/style.css',
                ],
            ],
        ];
    }
    
    // Production: Use versioned assets
    return [
        'asset' => [
            'resource_map' => [
                'sandbox/assets/css/plugins.css' => 'sandbox/assets/css/plugins-3a97ff4ee3.css',
                'sandbox/assets/css/style.css'   => 'sandbox/assets/css/style-b8c9d1e2f3.css',
            ],
        ],
    ];
}
```

## Benefits

1. **Cache Busting**: Browsers automatically download new versions when assets change
2. **Performance**: Browsers can cache assets for long periods (set far-future expires headers)
3. **CDN Friendly**: Works seamlessly with CDNs
4. **Version Control**: Easy to track which version of assets is deployed
5. **Rollback**: Easy to revert to previous asset versions

## Best Practices

1. **Always use the asset helper** in templates instead of hardcoded paths
2. **Generate hashes from file content** (MD5 or SHA1) for versioning
3. **Keep old versioned files** for a grace period to avoid 404s
4. **Use a build process** to automate asset versioning during deployment
5. **Set far-future cache headers** for versioned assets in your web server config

## Clearing Cache

After updating asset configuration, always clear the config cache:

```bash
docker exec neutrino_php php bin/clear-config-cache.php
```

## Current Assets Using Helper

- ✅ `sandbox/assets/css/plugins.css`
- ✅ `sandbox/assets/css/style.css`
- ✅ `sandbox/assets/js/plugins.js`
- ✅ `sandbox/assets/js/theme.js`

All asset paths in templates now use `$this->asset()` helper for consistent versioning support.

