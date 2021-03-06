<!DOCTYPE html><html lang="en"><head>
    <meta charset="utf-8">
    <title>SkongaWeb | School Management Information System</title>
    <meta name="description" content="The school management information system designed and developed specifically 
    to manage schools in Tanzania">
    <base href="/assets/angular/">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="./assets/images/icon.ico">
    <style type="text/css">@font-face{font-family:'Material Icons';font-style:normal;font-weight:400;src:url(https://fonts.gstatic.com/s/materialicons/v93/flUhRq6tzZclQEJ-Vdg-IuiaDsNa.woff) format('woff');}.material-icons{font-family:'Material Icons';font-weight:normal;font-style:normal;font-size:24px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;font-feature-settings:'liga';}@font-face{font-family:'Material Icons';font-style:normal;font-weight:400;src:url(https://fonts.gstatic.com/s/materialicons/v93/flUhRq6tzZclQEJ-Vdg-IuiaDsNcIhQ8tQ.woff2) format('woff2');}.material-icons{font-family:'Material Icons';font-weight:normal;font-style:normal;font-size:24px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;-webkit-font-feature-settings:'liga';-webkit-font-smoothing:antialiased;}</style>
    <style type="text/css">@font-face{font-family:'Archivo';font-style:italic;font-weight:400;font-stretch:normal;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k8o8UDI-1M0wlSfdzyIEkpwTM29hr-8mTYIRyOSVz60_PG_HCBsxdv.woff) format('woff');}@font-face{font-family:'Archivo';font-style:normal;font-weight:400;font-stretch:normal;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k6o8UDI-1M0wlSV9XAw6lQkqWY8Q82sJaRE-NWIDdgffTTNDNp8w.woff) format('woff');}@font-face{font-family:'Archivo';font-style:normal;font-weight:500;font-stretch:normal;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k6o8UDI-1M0wlSV9XAw6lQkqWY8Q82sJaRE-NWIDdgffTTBjNp8w.woff) format('woff');}@font-face{font-family:'Archivo';font-style:normal;font-weight:600;font-stretch:normal;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k6o8UDI-1M0wlSV9XAw6lQkqWY8Q82sJaRE-NWIDdgffTT6jRp8w.woff) format('woff');}@font-face{font-family:'Archivo';font-style:normal;font-weight:700;font-stretch:normal;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k6o8UDI-1M0wlSV9XAw6lQkqWY8Q82sJaRE-NWIDdgffTT0zRp8w.woff) format('woff');}@font-face{font-family:'Archivo';font-style:italic;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k8o8UDI-1M0wlSfdzyIEkpwTM29hr-8mTYIRyOSVz60_PG_HCBsydkD0nAUMxRMG4tuw.woff) format('woff');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Archivo';font-style:italic;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k8o8UDI-1M0wlSfdzyIEkpwTM29hr-8mTYIRyOSVz60_PG_HCBsydlD0nAUMxRMG4tuw.woff) format('woff');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Archivo';font-style:italic;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3k8o8UDI-1M0wlSfdzyIEkpwTM29hr-8mTYIRyOSVz60_PG_HCBsydrD0nAUMxRMG4.woff) format('woff');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Archivo';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLySOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Archivo';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLyTOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Archivo';font-style:normal;font-weight:400;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxKsv4Rn.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Archivo';font-style:normal;font-weight:500;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLySOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Archivo';font-style:normal;font-weight:500;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLyTOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Archivo';font-style:normal;font-weight:500;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxKsv4Rn.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Archivo';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLySOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Archivo';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLyTOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Archivo';font-style:normal;font-weight:600;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxKsv4Rn.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Archivo';font-style:normal;font-weight:700;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLySOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Archivo';font-style:normal;font-weight:700;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLyTOxKsv4RnUPU.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Archivo';font-style:normal;font-weight:700;font-stretch:100%;font-display:swap;src:url(https://fonts.gstatic.com/s/archivo/v8/k3kPo8UDI-1M0wlSV9XAw6lQkqWY8Q82sLydOxKsv4Rn.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}</style>

    <style>
      .app-loader {
        height: 100vh !important;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
      }
      .spinner {
        width: 40px;
        height: 40px;
        position: relative;
        margin: auto;
      }
      .double-bounce1,
      .double-bounce2 {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        opacity: 0.6;
        position: absolute;
        top: 0;
        left: 0;
        -webkit-animation: sk-bounce 2s infinite ease-in-out;
        animation: sk-bounce 2s infinite ease-in-out;
      }
      .double-bounce2 {
        -webkit-animation-delay: -1s;
        animation-delay: -1s;
      }
      @-webkit-keyframes sk-bounce {
        0%,
        100% {
          -webkit-transform: scale(0);
        }
        50% {
          -webkit-transform: scale(1);
        }
      }
      @keyframes sk-bounce {
        0%,
        100% {
          transform: scale(0);
          -webkit-transform: scale(0);
        }
        50% {
          transform: scale(1);
          -webkit-transform: scale(1);
        }
      }
    </style>
  <style>@charset "UTF-8";:root{--surface-a:#fff;--surface-b:#fafafa;--surface-c:rgba(0,0,0,.04);--surface-d:rgba(0,0,0,.12);--surface-e:#fff;--surface-f:#fff;--text-color:rgba(0,0,0,0.87);--text-color-secondary:textSecondaryColor;--primary-color:#3f51b5;--primary-color-text:#fff;--font-family:Roboto,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;--surface-0:#fff;--surface-50:#fafafa;--surface-100:#f5f5f5;--surface-200:#eee;--surface-300:#e0e0e0;--surface-400:#bdbdbd;--surface-500:#9e9e9e;--surface-600:#757575;--surface-700:#616161;--surface-800:#424242;--surface-900:#212121;--gray-50:#fafafa;--gray-100:#f5f5f5;--gray-200:#eee;--gray-300:#e0e0e0;--gray-400:#bdbdbd;--gray-500:#9e9e9e;--gray-600:#757575;--gray-700:#616161;--gray-800:#424242;--gray-900:#212121;--content-padding:1rem;--inline-spacing:0.5rem;}@font-face{font-family:Roboto;font-style:normal;font-weight:400;src:local("Roboto"),local("Roboto-Regular"),url(roboto-v20-latin-ext_latin-regular.5cb5c8f08bb4e6cb64c3.woff2) format("woff2"),url(roboto-v20-latin-ext_latin-regular.ae804dc012b1b5255474.woff) format("woff");}@font-face{font-family:Roboto;font-style:normal;font-weight:500;src:local("Roboto Medium"),local("Roboto-Medium"),url(roboto-v20-latin-ext_latin-500.0b45721325446d537b54.woff2) format("woff2"),url(roboto-v20-latin-ext_latin-500.e492ac63197a57e7f4d3.woff) format("woff");}@font-face{font-family:Roboto;font-style:normal;font-weight:700;src:local("Roboto Bold"),local("Roboto-Bold"),url(roboto-v20-latin-ext_latin-700.1d1ef7788f0ff084b881.woff2) format("woff2"),url(roboto-v20-latin-ext_latin-700.8aba6dc5d991e4367d7a.woff) format("woff");}*{box-sizing:border-box;}:root{--blue-50:#f4fafe;--blue-100:#cae6fc;--blue-200:#a0d2fa;--blue-300:#75bef8;--blue-400:#4baaf5;--blue-500:#2196f3;--blue-600:#1c80cf;--blue-700:#1769aa;--blue-800:#125386;--blue-900:#0d3c61;--green-50:#f7faf5;--green-100:#dbe8cf;--green-200:#bed6a9;--green-300:#a1c384;--green-400:#85b15e;--green-500:#689f38;--green-600:#588730;--green-700:#496f27;--green-800:#39571f;--green-900:#2a4016;--yellow-50:#fffcf5;--yellow-100:#fef0cd;--yellow-200:#fde4a5;--yellow-300:#fdd87d;--yellow-400:#fccc55;--yellow-500:#fbc02d;--yellow-600:#d5a326;--yellow-700:#b08620;--yellow-800:#8a6a19;--yellow-900:#644d12;--cyan-50:#f2fcfd;--cyan-100:#c2eff5;--cyan-200:#91e2ed;--cyan-300:#61d5e4;--cyan-400:#30c9dc;--cyan-500:#00bcd4;--cyan-600:#00a0b4;--cyan-700:#008494;--cyan-800:#006775;--cyan-900:#004b55;--pink-50:#fef4f7;--pink-100:#fac9da;--pink-200:#f69ebc;--pink-300:#f1749e;--pink-400:#ed4981;--pink-500:#e91e63;--pink-600:#c61a54;--pink-700:#a31545;--pink-800:#801136;--pink-900:#5d0c28;--indigo-50:#f6f7fc;--indigo-100:#d5d9ef;--indigo-200:#b3bae2;--indigo-300:#919cd5;--indigo-400:#707dc8;--indigo-500:#4e5fbb;--indigo-600:#42519f;--indigo-700:#374383;--indigo-800:#2b3467;--indigo-900:#1f264b;--teal-50:#f2faf9;--teal-100:#c2e6e2;--teal-200:#91d2cc;--teal-300:#61beb5;--teal-400:#30aa9f;--teal-500:#009688;--teal-600:#008074;--teal-700:#00695f;--teal-800:#00534b;--teal-900:#003c36;--orange-50:#fffaf2;--orange-100:#ffe6c2;--orange-200:#ffd391;--orange-300:#ffbf61;--orange-400:#ffac30;--orange-500:#ff9800;--orange-600:#d98100;--orange-700:#b36a00;--orange-800:#8c5400;--orange-900:#663d00;--bluegray-50:#f7f9f9;--bluegray-100:#d9e0e3;--bluegray-200:#bbc7cd;--bluegray-300:#9caeb7;--bluegray-400:#7e96a1;--bluegray-500:#607d8b;--bluegray-600:#526a76;--bluegray-700:#435861;--bluegray-800:#35454c;--bluegray-900:#263238;--purple-50:#faf4fb;--purple-100:#e7cbec;--purple-200:#d4a2dd;--purple-300:#c279ce;--purple-400:#af50bf;--purple-500:#9c27b0;--purple-600:#852196;--purple-700:#6d1b7b;--purple-800:#561561;--purple-900:#3e1046;}html{font-size:16px;}body,html{width:100%;position:relative;-webkit-tap-highlight-color:rgba(0,0,0,0);-webkit-touch-callout:none;-webkit-text-size-adjust:100%;-moz-text-size-adjust:100%;text-size-adjust:100%;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}body{margin:0;padding:0;font-weight:400;font-size:.875rem;line-height:1.5;font-family:Archivo,Helvetica Neue,sans-serif;}div{box-sizing:border-box;}.app-loader{height:100%;width:100%;position:absolute;top:0;left:0;display:flex;align-items:center;}.spinner{width:40px;height:40px;position:relative;margin:auto;}.double-bounce1,.double-bounce2{width:100%;height:100%;border-radius:50%;opacity:.6;position:absolute;top:0;left:0;-webkit-animation:sk-bounce 2s ease-in-out infinite;animation:sk-bounce 2s ease-in-out infinite;}.double-bounce2{-webkit-animation-delay:-1s;animation-delay:-1s;}@-webkit-keyframes sk-bounce{0%,to{-webkit-transform:scale(0);}50%{-webkit-transform:scale(1);}}@keyframes sk-bounce{0%,to{transform:scale(0);-webkit-transform:scale(0);}50%{transform:scale(1);-webkit-transform:scale(1);}}.egret-navy{color:rgba(0,0,0,.87);}.egret-navy .mat-bg-primary{background-color:#0081ff;}.egret-navy .mat-bg-accent{background-color:#ff8a48;}</style><link rel="stylesheet" href="/assets/angular/styles.26a4c77d3c53434f1fc5.css" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="/assets/angular/styles.26a4c77d3c53434f1fc5.css"></noscript></head>
  <body class="egret-navy">
    <app-root>
      <div class="app-loader">
        <div class="spinner">
          <div class="double-bounce1 mat-bg-primary" style="background: #fcc02e"></div>
          <div class="double-bounce2 mat-bg-accent" style="background: #03a9f4"></div>
        </div>
      </div>
    </app-root>

    <!-- <script>
    __Zone_enable_cross_context_check = true;
  </script> -->
  <script src="/assets/angular/runtime-es2015.c55b8a49641a432a6ff1.js" type="module"></script><script src="/assets/angular/runtime-es5.c55b8a49641a432a6ff1.js" nomodule defer></script><script src="/assets/angular/polyfills-es5.63fec0ea32bb7ed3b87e.js" nomodule defer></script><script src="/assets/angular/polyfills-es2015.2826b522269d92c3b4b7.js" type="module"></script><script src="/assets/angular/scripts.0b8bf7c81733953cefa8.js" defer></script><script src="/assets/angular/main-es2015.3a11e2c8419d04790381.js" type="module"></script><script src="/assets/angular/main-es5.3a11e2c8419d04790381.js" nomodule defer></script>

</body></html>