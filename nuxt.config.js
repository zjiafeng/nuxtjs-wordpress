const CompressionPlugin = require("compression-webpack-plugin");
export default {
  mode: 'universal',

  head: {
    title: 'Jiafeng Blog | WEB前端笔记',
    meta: [
      { name: 'referrer', content: 'no-referrer' },
      { charset: 'utf-8' },
      { name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' },
      { name: 'format-detection', content: 'telephone=no' }
    ],
    link: [
      { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }
      // 改用 highlight.js  layouts/page.vue
      // { hid: 'prism', rel: 'stylesheet', href: '/css/prism.css' }
    ],
    script: [
      // 彩色图标
      { src: 'https://at.alicdn.com/t/font_556506_1n415osdgrl.js' },
      // { src: '/js/prism.js' },
      // 百度主推文章收录用
      { src: 'https://zz.bdstatic.com/linksubmit/push.js' },
      // 加入百度统计js，使用时自行添加为自己的
      { src: 'https://hm.baidu.com/hm.js?fede818b3989d9a55d75f9ac72143da5' },
      { src: 'https://cdn.jsdelivr.net/gh/SGYZ0910/CDN@1.0.5/js/love.js' }
    ]
  },

  loading: './components/Loading',

  router: {
    middleware: 'global'
  },

  css: [
    'element-ui/lib/theme-chalk/index.css',
    './assets/scss/index.scss'
  ],

  styleResources: {
    scss: ['./assets/scss/variable.scss']
  },

  plugins: [
    '~/plugins/axios',
    { src: '~/plugins/element-ui', ssr: true },
    { src: '~/plugins/message', ssr: false },
    { src: '~/plugins/icon', ssr: true }
    // { src: '~/plugins/common', ssr: false }
  ],

  modules: [
    // Doc: https://axios.nuxtjs.org/usage
    '@nuxtjs/axios',
    '@nuxtjs/style-resources',
    '@nuxtjs/proxy',
    'nuxt-precompress'
  ],

  axios: {
    proxy: true
  },

  build: {
    transpile: [/^element-ui/],
    extractCSS: true,
    vendors: ['@nuxtjs/axios', 'element-ui'],
    plugins: [
      new CompressionPlugin({
        test: /\.js$|\.html$|\.css/, // 匹配文件名
        threshold: 10240, // 对超过10kb的数据进行压缩
        deleteOriginalAssets: false, // 是否删除原文件
      }),
    ],
    // extend(config, ctx) {
    // }
  },

  // 将此处2个地址改为自己的地址
  proxy: {
    '/api': {
      target: 'https://www.jiafeng.fun',
      pathRewrite: {
        '^/api': '/'
      }
    },
    '/wp-content': {
      target: 'https://www.jiafeng.fun'
    }
  },

  env: {
    baseUrl: '/api'
  },

  nuxtPrecompress: {
    gzip: {
      enabled: true,
      filename: '[path].gz[query]',
      threshold: 10240,
      minRatio: 0.8,
      compressionOptions: { level: 9 },
    },
    brotli: {
      enabled: true,
      filename: '[path].br[query]',
      compressionOptions: { level: 11 },
      threshold: 10240,
      minRatio: 0.8,
    },
    enabled: true,
    report: false,
    test: /\.(js|css|html|txt|xml|svg)$/,
    // Serving options
    middleware: {
      enabled: true,
      enabledStatic: true,
      encodingsPriority: ['br', 'gzip'],
    },
  },

}
