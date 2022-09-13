// 获取当前实际主题的函数
export function getNowTheme() {
    let nowTheme = document.body.getAttribute('data-theme');
    if (nowTheme === 'auto') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    } else {
        return nowTheme === 'dark' ? 'dark' : 'light';
    }
}

// 改变主题的事件
export function switchTheme() {
    let nowTheme = getNowTheme();
    let domTheme = document.body.getAttribute('data-theme');
    let systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

    if (domTheme === 'auto') {
        // 如果当前为自动模式，切换至用户选择的模式
        document.body.setAttribute('data-theme', nowTheme === 'light' ? 'dark' : 'light');
        localStorage.setItem('JiaFengBlog_data-theme', nowTheme === 'light' ? 'dark' : 'light');
    } else if (domTheme === 'light') {
        // 如果当前不为自动模式，且将要切换至 dark 模式
        document.body.setAttribute('data-theme', 'dark');
        // 如果将要切换至的 dark 模式是系统当前的模式
        localStorage.setItem('JiaFengBlog_data-theme', systemTheme === 'dark' ? 'auto' : 'dark');
    } else {
        // 同上 else if
        document.body.setAttribute('data-theme', 'light');
        localStorage.setItem('JiaFengBlog_data-theme', systemTheme === 'light' ? 'auto' : 'light');
    }
}