
    (function() {
      var cdnOrigin = "https://cdn.shopify.com";
      var scripts = ["cdn/shopifycloud/checkout-web/assets/c1/polyfills-legacy.B4Juc3Bz.js","cdn/shopifycloud/checkout-web/assets/c1/app-legacy.CKH509Zu.js","/cdn/shopifycloud/checkout-web/assets/c1/vendor-legacy.C6qkceQc.js","/cdn/shopifycloud/checkout-web/assets/c1/locale-en-legacy.4smU3Y4l.js","/cdn/shopifycloud/checkout-web/assets/c1/page-OnePage-legacy.CAmyPlsA.js","/cdn/shopifycloud/checkout-web/assets/c1/AddDiscountButton-legacy.BrlMI6BF.js","/cdn/shopifycloud/checkout-web/assets/c1/NumberField-legacy.kd5k7BL6.js","/cdn/shopifycloud/checkout-web/assets/c1/useShowShopPayOptin-legacy.ClntGIsc.js","/cdn/shopifycloud/checkout-web/assets/c1/ShopPayOptInDisclaimer-legacy.DDIqd7Us.js","/cdn/shopifycloud/checkout-web/assets/c1/RememberMeDescriptionText-legacy.065mhPWL.js","/cdn/shopifycloud/checkout-web/assets/c1/SeparatePaymentsNotice-legacy.9kRwijwa.js","/cdn/shopifycloud/checkout-web/assets/c1/StockProblemsLineItemList-legacy.DJIm8Y_l.js","/cdn/shopifycloud/checkout-web/assets/c1/LocalPickup-legacy.Dtp1JUY1.js","/cdn/shopifycloud/checkout-web/assets/c1/useShopPayButtonClassName-legacy.CdizRUN4.js","/cdn/shopifycloud/checkout-web/assets/c1/VaultedPayment-legacy.B1oSb6vL.js","/cdn/shopifycloud/checkout-web/assets/c1/useAddressManager-legacy.13_KbC0u.js","/cdn/shopifycloud/checkout-web/assets/c1/useShopPayPaymentRequiredMethod-legacy.MOU4vPrw.js","/cdn/shopifycloud/checkout-web/assets/c1/PayButtonSection-legacy.Dx3d6Xe5.js","/cdn/shopifycloud/checkout-web/assets/c1/ShipmentBreakdown-legacy.jGE5msUg.js","/cdn/shopifycloud/checkout-web/assets/c1/MerchandiseModal-legacy.DHM-kHmn.js","/cdn/shopifycloud/checkout-web/assets/c1/StackedMerchandisePreview-legacy.CbJzbnF4.js","/cdn/shopifycloud/checkout-web/assets/c1/component-ShopPayVerificationSwitch-legacy.DztYzdFx.js","/cdn/shopifycloud/checkout-web/assets/c1/useSuppressShopPayModalOnLoad-legacy.A-hZZWIN.js","/cdn/shopifycloud/checkout-web/assets/c1/useSubscribeMessenger-legacy.DK82orZd.js","/cdn/shopifycloud/checkout-web/assets/c1/shop-js-index-legacy.B-2hZCMD.js","/cdn/shopifycloud/checkout-web/assets/c1/v4-legacy.On_frbc2.js","/cdn/shopifycloud/checkout-web/assets/c1/component-RuntimeExtension-legacy.-5Iy1DKp.js","/cdn/shopifycloud/checkout-web/assets/c1/AnnouncementRuntimeExtensions-legacy.8rf76juG.js","/cdn/shopifycloud/checkout-web/assets/c1/Switch-legacy.C9JpOgI4.js","/cdn/shopifycloud/checkout-web/assets/c1/rendering-extension-targets-legacy.CfiDzK6Z.js","/cdn/shopifycloud/checkout-web/assets/c1/ExtensionsInner-legacy.DN5OCwUP.js"];
      var styles = [];
      var fontPreconnectUrls = [];
      var fontPrefetchUrls = [];
      var imgPrefetchUrls = ["https://cdn.shopify.com/s/files/1/0081/3305/0458/files/Home-6-Logo-Parallax_x320.png?v=1638535058"];

      function preconnect(url, callback) {
        var link = document.createElement('link');
        link.rel = 'dns-prefetch preconnect';
        link.href = url;
        link.crossOrigin = '';
        link.onload = link.onerror = callback;
        document.head.appendChild(link);
      }

      function preconnectAssets() {
        var resources = [cdnOrigin].concat(fontPreconnectUrls);
        var index = 0;
        (function next() {
          var res = resources[index++];
          if (res) preconnect(res, next);
        })();
      }

      function prefetch(url, as, callback) {
        var link = document.createElement('link');
        if (link.relList.supports('prefetch')) {
          link.rel = 'prefetch';
          link.fetchPriority = 'low';
          link.as = as;
          if (as === 'font') link.type = 'font/woff2';
          link.href = url;
          link.crossOrigin = '';
          link.onload = link.onerror = callback;
          document.head.appendChild(link);
        } else {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', url, true);
          xhr.onloadend = callback;
          xhr.send();
        }
      }

      function prefetchAssets() {
        var resources = [].concat(
          scripts.map(function(url) { return [url, 'script']; }),
          styles.map(function(url) { return [url, 'style']; }),
          fontPrefetchUrls.map(function(url) { return [url, 'font']; }),
          imgPrefetchUrls.map(function(url) { return [url, 'image']; })
        );
        var index = 0;
        function run() {
          var res = resources[index++];
          if (res) prefetch(res[0], res[1], next);
        }
        var next = (self.requestIdleCallback || setTimeout).bind(self, run);
        next();
      }

      function onLoaded() {
        try {
          if (parseFloat(navigator.connection.effectiveType) > 2 && !navigator.connection.saveData) {
            preconnectAssets();
            prefetchAssets();
          }
        } catch (e) {}
      }

      if (document.readyState === 'complete') {
        onLoaded();
      } else {
        addEventListener('load', onLoaded);
      }
    })();
  