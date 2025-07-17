class App {
    init() {
        this.initComponents(),
            this.initPreloader(),
            this.initPortletCard(),
            this.initMultiDropdown(),
            this.initFormValidation(),
            this.initCounter(),
            this.initCodePreview(),
            this.initToggle(),
            this.initDismissible(),
            this.initLeftSidebar(),
            this.initTopbarMenu()
    }
    initComponents() {
        "function" == typeof lucide.createIcons && lucide.createIcons(),
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(e => {
                    new bootstrap.Popover(e)
                }
            ),
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(e => {
                    new bootstrap.Tooltip(e)
                }
            ),
            document.querySelectorAll(".offcanvas").forEach(e => {
                    new bootstrap.Offcanvas(e)
                }
            ),
            document.querySelectorAll(".toast").forEach(e => {
                    new bootstrap.Toast(e)
                }
            )
    }
    initPreloader() {
        window.addEventListener("load", () => {
                var e = document.getElementById("status");
                let t = document.getElementById("preloader");
                e && (e.style.display = "none"),
                t && setTimeout( () => t.style.display = "none", 350)
            }
        )
    }
    initPortletCard() {
        $('[data-action="card-close"]').on("click", function(e) {
            e.preventDefault();
            let t = $(this).closest(".card");
            t.fadeOut(300, function() {
                t.remove()
            })
        }),
            $('[data-action="card-toggle"]').on("click", function(e) {
                e.preventDefault();
                var e = $(this).closest(".card")
                    , t = $(this).find("i").eq(0)
                    , a = e.find(".card-body")
                    , i = e.find(".card-footer");
                a.slideToggle(300),
                    i.slideToggle(200),
                    t.toggleClass("ti-chevron-up ti-chevron-down"),
                    e.toggleClass("card-collapse")
            });
        var e = document.querySelectorAll('[data-action="card-refresh"]');
        e && e.forEach(function(e) {
            e.addEventListener("click", function(e) {
                e.preventDefault();
                var t, e = e.target.closest(".card");
                let a = e.querySelector(".card-overlay");
                a || ((a = document.createElement("div")).classList.add("card-overlay"),
                    (t = document.createElement("div")).classList.add("spinner-border", "text-primary"),
                    a.appendChild(t),
                    e.appendChild(a)),
                    a.style.display = "flex",
                    setTimeout(function() {
                        a.style.display = "none"
                    }, 1500)
            })
        }),
            $('[data-action="code-collapse"]').on("click", function(e) {
                e.preventDefault();
                var e = $(this).closest(".card")
                    , t = $(this).find("i").eq(0);
                e.find(".code-body").slideToggle(300),
                    t.toggleClass("ti-chevron-up ti-chevron-down")
            })
    }
    initMultiDropdown() {
        $(".dropdown-menu a.dropdown-toggle").on("click", function() {
            var e = $(this).next(".dropdown-menu")
                , e = $(this).parent().parent().find(".dropdown-menu").not(e);
            return e.removeClass("show"),
                e.parent().find(".dropdown-toggle").removeClass("show"),
                !1
        })
    }
    initFormValidation() {
        document.querySelectorAll(".needs-validation").forEach(t => {
                t.addEventListener("submit", e => {
                        t.checkValidity() || (e.preventDefault(),
                            e.stopPropagation()),
                            t.classList.add("was-validated")
                    }
                    , !1)
            }
        )
    }
    initCounter() {
        var e = document.querySelectorAll("[data-target]");
        let t = new IntersectionObserver( (e, n) => {
                e.forEach(o => {
                        if (o.isIntersecting) {
                            let e = o.target, t = e.getAttribute("data-target").replace(/,/g, ""), a = (t = parseFloat(t),
                                0), i, s = (i = Number.isInteger(t) ? Math.floor(t / 25) : t / 25,
                                    () => {
                                        a < t ? ((a += i) > t && (a = t),
                                            e.innerText = r(a),
                                            requestAnimationFrame(s)) : e.innerText = r(t)
                                    }
                            );
                            function r(e) {
                                return e % 1 == 0 ? e.toLocaleString() : e.toLocaleString(void 0, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })
                            }
                            s(),
                                n.unobserve(e)
                        }
                    }
                )
            }
            ,{
                threshold: 1
            });
        e.forEach(e => t.observe(e))
    }
    initCodePreview() {
        "undefined" != typeof Prism && Prism.plugins && Prism.plugins.NormalizeWhitespace && Prism.plugins.NormalizeWhitespace.setDefaults({
            "remove-trailing": !0,
            "remove-indent": !0,
            "left-trim": !0,
            "right-trim": !0,
            "tabs-to-spaces": 4,
            "spaces-to-tabs": 4
        })
    }
    initToggle() {
        document.querySelectorAll("[data-toggler]").forEach(e => {
                let t = e.querySelector("[data-toggler-on]")
                    , a = e.querySelector("[data-toggler-off]")
                    , i = "on" === e.getAttribute("data-toggler")
                    , s = () => {
                        i ? (t?.classList.remove("d-none"),
                            a?.classList.add("d-none")) : (t?.classList.add("d-none"),
                            a?.classList.remove("d-none"))
                    }
                ;
                t?.addEventListener("click", () => {
                        i = !1,
                            s()
                    }
                ),
                    a?.addEventListener("click", () => {
                            i = !0,
                                s()
                        }
                    ),
                    s()
            }
        )
    }
    initDismissible() {
        document.querySelectorAll("[data-dismissible]").forEach(t => {
                t.addEventListener("click", () => {
                        var e = t.getAttribute("data-dismissible")
                            , e = document.querySelector(e);
                        e && e.remove()
                    }
                )
            }
        )
    }
    initLeftSidebar() {
        let o = document.querySelector(".side-nav");
        if (o) {
            o.querySelectorAll("li [data-bs-toggle='collapse']").forEach(e => {
                    e.addEventListener("click", e => e.preventDefault())
                }
            );
            let s = o.querySelectorAll("li .collapse")
                , e = (s.forEach(e => {
                    e.addEventListener("show.bs.collapse", e => {
                            let t = e.target
                                , a = []
                                , i = t.parentElement;
                            for (; i && i !== o; )
                                i.classList.contains("collapse") && a.push(i),
                                    i = i.parentElement;
                            s.forEach(e => {
                                    e === t || a.includes(e) || new bootstrap.Collapse(e,{
                                        toggle: !1
                                    }).hide()
                                }
                            )
                        }
                    )
                }
            ),
                window.location.href.split(/[?#]/)[0]);
            o.querySelectorAll("a").forEach(t => {
                    if (t.href === e) {
                        o.querySelectorAll("a.active, li.active, .collapse.show").forEach(e => {
                                e.classList.remove("active"),
                                    e.classList.remove("show")
                            }
                        ),
                            t.classList.add("active");
                        let e = t.closest("li");
                        for (; e && e !== o; ) {
                            e.classList.add("active");
                            var a = e.closest(".collapse");
                            e = a ? (new bootstrap.Collapse(a,{
                                toggle: !1
                            }).show(),
                            (a = a.closest("li")) && a.classList.add("active"),
                                a) : e.parentElement
                        }
                    }
                }
            ),
                setTimeout( () => {
                        var e = o.querySelector("li.active .active")
                            , t = document.querySelector(".sidenav-menu .simplebar-content-wrapper");
                        if (e && t) {
                            e = e.offsetTop - 300;
                            if (100 < e) {
                                var n = t;
                                t = e;
                                var l = 600;
                                let s = n.scrollTop
                                    , o = t - s
                                    , r = 0;
                                !function e() {
                                    var t, a, i;
                                    r += 20,
                                        n.scrollTop = (t = r,
                                            a = s,
                                            i = o,
                                            (t /= l / 2) < 1 ? i / 2 * t * t + a : -i / 2 * (--t * (t - 2) - 1) + a),
                                    r < l && setTimeout(e, 20)
                                }()
                            }
                        }
                    }
                    , 200)
        }
    }
    initTopbarMenu() {
        var i = document.querySelector(".navbar-nav");
        if (i) {
            let t = window.location.href.split(/[?#]/)[0];
            i.querySelectorAll("li a").forEach(e => {
                    if (e.href === t) {
                        e.classList.add("active");
                        let t = e.parentElement;
                        for (let e = 0; e < 6 && t && t !== document.body; e++)
                            "LI" !== t.tagName && !t.classList.contains("dropdown") || t.classList.add("active"),
                                t = t.parentElement
                    }
                }
            );
            let e = document.querySelector(".navbar-toggle")
                , a = document.getElementById("navigation");
            e && a && e.addEventListener("click", () => {
                    e.classList.toggle("open"),
                        "block" === a.style.display ? a.style.display = "none" : a.style.display = "block"
                }
            )
        }
    }
}
let skinPresets = {
    classic: {
        theme: "light",
        topbar: "light",
        menu: "dark",
        sidenav: {
            user: !0
        }
    },
    modern: {
        theme: "light",
        topbar: "light",
        menu: "gradient",
        sidenav: {
            user: !0
        }
    },
    material: {
        theme: "light",
        topbar: "dark",
        menu: "light",
        sidenav: {
            user: !0
        }
    },
    saas: {
        theme: "light",
        topbar: "light",
        menu: "dark",
        sidenav: {
            user: !0
        }
    },
    minimal: {
        theme: "light",
        topbar: "light",
        menu: "gray",
        sidenav: {
            user: !1
        }
    },
    flat: {
        theme: "light",
        topbar: "light",
        menu: "dark",
        sidenav: {
            user: !1
        }
    }
};
class LayoutCustomizer {
    constructor() {
        this.html = document.documentElement,
            this.config = {}
    }
    init() {
        this.initConfig(),
            this.initSwitchListener(),
            this.initWindowSize(),
            this._adjustLayout(),
            this.setSwitchFromConfig(),
            this.openCustomizer()
    }
    initConfig() {
        this.defaultConfig = JSON.parse(JSON.stringify(window.defaultConfig)),
            this.config = JSON.parse(JSON.stringify(window.config)),
            this.setSwitchFromConfig()
    }
    isFirstVisit() {
        return !localStorage.getItem("__user_has_visited__") && (localStorage.setItem("__user_has_visited__", "true"),
            !0)
    }
    openCustomizer() {
        var t = document.getElementById("theme-settings-offcanvas");
        if (t && this.isFirstVisit()) {
            let e = new bootstrap.Offcanvas(t);
            setTimeout( () => {
                    e.show()
                }
                , 1e3)
        }
    }
    applyPreset(e) {
        e = skinPresets?.[e];
        e && (e.theme && (this.config.theme = e.theme,
            this.html.setAttribute("data-bs-theme", e.theme)),
        e.topbar && (this.config.topbar.color = e.topbar,
            this.html.setAttribute("data-topbar-color", e.topbar)),
        e.menu && (this.config.menu.color = e.menu,
            this.html.setAttribute("data-menu-color", e.menu)),
        e.sidenav?.size && (this.config.sidenav.size = e.sidenav.size,
            this.html.setAttribute("data-sidenav-size", e.sidenav.size)),
        void 0 !== e.sidenav?.user) && (this.config.sidenav.user = e.sidenav.user,
            e.sidenav.user ? this.html.setAttribute("data-sidenav-user", "true") : this.html.removeAttribute("data-sidenav-user"))
    }
    changeSkin(e) {
        this.config.skin = e,
            this.html.setAttribute("data-skin", e),
            this.applyPreset(e),
            this.setSwitchFromConfig()
    }
    changeMenuColor(e) {
        this.config.menu.color = e,
            this.html.setAttribute("data-menu-color", e),
            this.setSwitchFromConfig()
    }
    changeLeftbarSize(e, t=!0) {
        this.html.setAttribute("data-sidenav-size", e),
        t && (this.config.sidenav.size = e,
            this.setSwitchFromConfig())
    }
    changeLayoutPosition(e) {
        this.config.layout.position = e,
            this.html.setAttribute("data-layout-position", e),
            this.setSwitchFromConfig()
    }
    getSystemTheme() {
        return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"
    }
    changeTheme(e) {
        "system" === e && this.getSystemTheme();
        this.config.theme = e,
            this.html.setAttribute("data-bs-theme", "system" === e ? this.getSystemTheme() : e),
            this.setSwitchFromConfig()
    }
    changeTopbarColor(e) {
        this.config.topbar.color = e,
            this.html.setAttribute("data-topbar-color", e),
            this.setSwitchFromConfig()
    }
    changeSidebarUser(e) {
        (this.config.sidenav.user = e) ? this.html.setAttribute("data-sidenav-user", e) : this.html.removeAttribute("data-sidenav-user"),
            this.setSwitchFromConfig()
    }
    resetTheme() {
        this.config = JSON.parse(JSON.stringify(window.defaultConfig)),
            this.changeSkin(this.config.skin),
            this.changeMenuColor(this.config.menu.color),
            this.changeLeftbarSize(this.config.sidenav.size),
            this.changeTheme(this.config.theme),
            this.changeLayoutPosition(this.config.layout.position),
            this.changeTopbarColor(this.config.topbar.color),
            this.changeSidebarUser(this.config.sidenav.user),
            this._adjustLayout()
    }
    setSwitchFromConfig() {
        var e = this.config;
        sessionStorage.setItem("__INSPINIA_CONFIG__", JSON.stringify(e)),
            document.querySelectorAll("#theme-settings-offcanvas input[type=radio]").forEach(e => e.checked = !1);
        ( (e, t) => {
                e = document.querySelector(e);
                e && (e.checked = t)
            }
        )('input[name="sidebar-user"]', !0 === e.sidenav.user),
            [["data-skin", e.skin], ["data-bs-theme", e.theme], ["data-layout-position", e.layout.position], ["data-topbar-color", e.topbar.color], ["data-menu-color", e.menu.color], ["data-sidenav-size", e.sidenav.size]].forEach( ([e,t]) => {
                    e = document.querySelector(`input[name="${e}"][value="${t}"]`);
                    e && (e.checked = !0)
                }
            )
    }
    initSwitchListener() {
        var e = (e, t) => {
            document.querySelectorAll(e).forEach(e => e.addEventListener("change", () => t(e)))
        }
            , e = (e('input[name="data-skin"]', e => this.changeSkin(e.value)),
            e('input[name="data-bs-theme"]', e => this.changeTheme(e.value)),
            e('input[name="data-menu-color"]', e => this.changeMenuColor(e.value)),
            e('input[name="data-sidenav-size"]', e => this.changeLeftbarSize(e.value)),
            e('input[name="data-layout-position"]', e => this.changeLayoutPosition(e.value)),
            e('input[name="data-topbar-color"]', e => this.changeTopbarColor(e.value)),
            e('input[name="sidebar-user"]', e => this.changeSidebarUser(e.checked)),
            document.getElementById("light-dark-mode"))
            , e = (e && e.addEventListener("click", () => {
                var e = "light" === this.config.theme ? "dark" : "light";
                this.changeTheme(e)
            }
        ),
            document.querySelector("#reset-layout"))
            , e = (e && e.addEventListener("click", () => this.resetTheme()),
            document.querySelector(".sidenav-toggle-button"))
            , e = (e && e.addEventListener("click", () => this._toggleSidebar()),
            document.querySelector(".button-close-offcanvas"));
        e && e.addEventListener("click", () => {
                this.html.classList.remove("sidebar-enable"),
                    this.hideBackdrop()
            }
        ),
            document.querySelectorAll(".button-on-hover").forEach(e => {
                    e.addEventListener("click", () => {
                            var e = this.html.getAttribute("data-sidenav-size");
                            this.changeLeftbarSize("on-hover-active" === e ? "on-hover" : "on-hover-active", !0)
                        }
                    )
                }
            )
    }
    _toggleSidebar() {
        var e = this.html.getAttribute("data-sidenav-size")
            , t = this.config.sidenav.size;
        "offcanvas" === e ? this.showBackdrop() : "compact" === t ? this.changeLeftbarSize("condensed" === e ? "compact" : "condensed", !1) : this.changeLeftbarSize("condensed" === e ? "default" : "condensed", !0),
            this.html.classList.toggle("sidebar-enable")
    }
    showBackdrop() {
        var e = document.createElement("div");
        e.id = "custom-backdrop",
            e.className = "offcanvas-backdrop fade show",
            document.body.appendChild(e),
            document.body.style.overflow = "hidden",
        767 < window.innerWidth && (document.body.style.paddingRight = "15px"),
            e.addEventListener("click", () => {
                    this.html.classList.remove("sidebar-enable"),
                        this.hideBackdrop()
                }
            )
    }
    hideBackdrop() {
        var e = document.getElementById("custom-backdrop");
        e && (document.body.removeChild(e),
            document.body.style.overflow = "",
            document.body.style.paddingRight = "")
    }
    _adjustLayout() {
        var e = window.innerWidth
            , t = this.config.sidenav.size;
        e <= 767.98 ? this.changeLeftbarSize("offcanvas", !1) : e <= 1140 && !["offcanvas"].includes(t) ? this.changeLeftbarSize("condensed", !1) : this.changeLeftbarSize(t)
    }
    initWindowSize() {
        window.addEventListener("resize", () => this._adjustLayout())
    }
}
class Plugins {
    init() {
        this.initFlatPicker(),
            this.initTouchSpin()
    }
    initFlatPicker() {
        document.querySelectorAll("[data-provider]").forEach(e => {
                var t = e.getAttribute("data-provider")
                    , a = e.attributes
                    , i = {
                    disableMobile: !0,
                    defaultDate: new Date
                };
                "flatpickr" === t ? (a["data-date-format"] && (i.dateFormat = a["data-date-format"].value),
                a["data-enable-time"] && (i.enableTime = !0,
                    i.dateFormat += " H:i"),
                a["data-altFormat"] && (i.altInput = !0,
                    i.altFormat = a["data-altFormat"].value),
                a["data-minDate"] && (i.minDate = a["data-minDate"].value),
                a["data-maxDate"] && (i.maxDate = a["data-maxDate"].value),
                a["data-default-date"] && (i.defaultDate = a["data-default-date"].value),
                a["data-multiple-date"] && (i.mode = "multiple"),
                a["data-range-date"] && (i.mode = "range"),
                a["data-inline-date"] && (i.inline = !0,
                    i.defaultDate = a["data-default-date"].value),
                a["data-disable-date"] && (i.disable = a["data-disable-date"].value.split(",")),
                a["data-week-number"] && (i.weekNumbers = !0),
                    flatpickr(e, i)) : "timepickr" === t && (i = {
                    enableTime: !0,
                    noCalendar: !0,
                    dateFormat: "H:i",
                    defaultDate: new Date
                },
                a["data-time-hrs"] && (i.time_24hr = !0),
                a["data-min-time"] && (i.minTime = a["data-min-time"].value),
                a["data-max-time"] && (i.maxTime = a["data-max-time"].value),
                a["data-default-time"] && (i.defaultDate = a["data-default-time"].value),
                a["data-time-inline"] && (i.inline = !0,
                    i.defaultDate = a["data-time-inline"].value),
                    flatpickr(e, i))
            }
        )
    }
    initTouchSpin() {
        document.querySelectorAll("[data-touchspin]").forEach(e => {
                var r = e.querySelector("[data-minus]")
                    , n = e.querySelector("[data-plus]");
                let l = e.querySelector("input");
                if (l) {
                    let t = Number(l.min)
                        , a = Number(l.max ?? 0)
                        , i = Number.isFinite(t)
                        , s = Number.isFinite(a)
                        , o = () => Number.parseInt(l.value) || 0;
                    l.hasAttribute("readonly") || (r && r.addEventListener("click", () => {
                            var e = o() - 1;
                            (!i || e >= t) && (l.value = e.toString())
                        }
                    ),
                    n && n.addEventListener("click", () => {
                            var e = o() + 1;
                            (!s || e <= a) && (l.value = e.toString())
                        }
                    ))
                }
            }
        )
    }
}
class I18nManager {
    constructor({defaultLang: e="cn", langPath: t="assets/data/translations/", langImageSelector: a="#selected-language-image", langCodeSelector: i="#selected-language-code", translationKeySelector: s="[data-lang]", translationKeyAttribute: o="data-lang", languageSelector: r="[data-translator-lang]"}={}) {
        this.selectedLanguage = sessionStorage.getItem("__INSPINIA_LANG__") || e,
            this.langPath = t,
            this.langImageSelector = a,
            this.langCodeSelector = i,
            this.translationKeySelector = s,
            this.translationKeyAttribute = o,
            this.languageSelector = r,
            this.selectedLanguageImage = document.querySelector(this.langImageSelector),
            this.selectedLanguageCode = document.querySelector(this.langCodeSelector),
            this.languageButtons = document.querySelectorAll(this.languageSelector)
    }
    async init() {
        await this.applyTranslations(),
            this.updateSelectedImage(),
            this.updateSelectedCode(),
            this.bindLanguageSwitchers()
    }
    async loadTranslations() {
        try {
            var e = await fetch("" + this.langPath + this.selectedLanguage + ".json");
            if (e.ok)
                return await e.json();
            throw new Error(`Failed to load ${this.selectedLanguage}.json`)
        } catch (e) {
            return console.error("Translation load error:", e),
                {}
        }
    }
    async applyTranslations() {
        let i = await this.loadTranslations();
        document.querySelectorAll(this.translationKeySelector).forEach(e => {
                var t = e.getAttribute(this.translationKeyAttribute)
                    , a = (a = i,
                    t.split(".").reduce( (e, t) => e?.[t] ?? null, a));
                a ? e.innerHTML = a : console.warn("Missing translation for key: " + t)
            }
        )
    }
    setLanguage(e) {
        this.selectedLanguage = e,
            localStorage.setItem("__INSPINIA_LANG__", e),
            this.applyTranslations(),
            this.updateSelectedImage(),
            this.updateSelectedCode()
    }
    updateSelectedImage() {
        var e = document.querySelector(`[data-translator-lang="${this.selectedLanguage}"] [data-translator-image]`);
        e && this.selectedLanguageImage && (this.selectedLanguageImage.src = e.src)
    }
    updateSelectedCode() {
        this.selectedLanguageCode && (this.selectedLanguageCode.textContent = this.selectedLanguage.toUpperCase())
    }
    bindLanguageSwitchers() {
        this.languageButtons.forEach(t => {
                t.addEventListener("click", () => {
                        var e = t.dataset.translatorLang;
                        e && e !== this.selectedLanguage && this.setLanguage(e)
                    }
                )
            }
        )
    }
}
document.addEventListener("DOMContentLoaded", function(e) {
    (new App).init(),
        (new LayoutCustomizer).init(),
        (new Plugins).init(),
        (new I18nManager).init()
});
let ins = (e, t=1) => {
        var a = getComputedStyle(document.documentElement).getPropertyValue("--ins-" + e).trim();
        return e.includes("-rgb") ? `rgba(${a}, ${t})` : a
    }
;
function debounce(e, t) {
    let a;
    return function() {
        clearTimeout(a),
            a = setTimeout(e, t)
    }
}
class CustomApexChart {
    constructor({selector: e, series: t=[], options: a={}, colors: i=[]}) {
        if (e) {
            this.selector = e,
                this.series = t,
                this.getOptions = a,
                this.colors = i,
                this.selector instanceof HTMLElement ? this.element = this.selector : this.element = document.querySelector(this.selector),
                this.chart = null;
            try {
                this.render(),
                    CustomApexChart.instances.push(this)
            } catch (e) {
                console.error("CustomApexChart: Error during chart initialization:", e)
            }
        } else
            console.warn("CustomApexChart: 'selector' is required.")
    }
    getColors() {
        var e = this.getOptions();
        if (e?.colors?.length)
            return e.colors;
        if (this.element) {
            e = this.element.getAttribute("data-colors");
            if (e) {
                e = e.split(",").map(e => e.trim()).map(e => e.startsWith("#") || e.includes("rgb") ? e : ins(e));
                if (e.length)
                    return e
            }
        }
        return [ins("primary"), ins("secondary"), ins("danger")]
    }
    render() {
        if (this.chart && this.chart.destroy(),
            this.element) {
            let e = JSON.parse(JSON.stringify(this.getOptions()));
            e.colors = this.getColors(),
            (e = this.injectDynamicColors(e, e.colors)).series || (e.series = this.series),
                this.chart = new ApexCharts(this.element,e),
                this.chart.render()
        } else
            console.warn(`CustomApexChart: No element found for selector '${this.selector}'.`)
    }
    injectDynamicColors(e, a) {
        var t;
        return "boxplot" === e.chart?.type?.toLowerCase() && (e.plotOptions = e.plotOptions || {},
            e.plotOptions.boxPlot = e.plotOptions.boxPlot || {},
            e.plotOptions.boxPlot.colors = e.plotOptions.boxPlot.colors || {},
            e.plotOptions.boxPlot.colors.upper = e.plotOptions.boxPlot.colors.upper || a[0],
            e.plotOptions.boxPlot.colors.lower = e.plotOptions.boxPlot.colors.lower || a[1]),
        Array.isArray(e.yaxis) && e.yaxis.forEach( (e, t) => {
                t = a[t] || this.colors[t] || "#999";
                e.axisBorder = e.axisBorder || {},
                    e.axisBorder.color = e.axisBorder.color || t,
                    e.labels = e.labels || {},
                    e.labels.style = e.labels.style || {},
                    e.labels.style.color = e.labels.style.color || t
            }
        ),
        e.markers && !e.markers.strokeColor && (e.markers.strokeColor = a),
        "gradient" === e.fill?.type && e.fill.gradient && (e.fill.gradient.gradientToColors = e.fill.gradient.gradientToColors || a),
        e.plotOptions?.treemap?.colorScale?.ranges && (0 < (t = e.plotOptions.treemap.colorScale.ranges).length && !t[0].color && (t[0].color = a[0]),
        1 < t.length) && !t[1].color && (t[1].color = a[1]),
            e
    }
    static rerenderAll() {
        if (0 < CustomApexChart.instances.length)
            for (var e of CustomApexChart.instances)
                e.render()
    }
}
class CustomEChart {
    constructor({selector: e, options: t={}, theme: a=null, initOptions: i={}}) {
        if (e) {
            this.selector = e,
                this.element = null,
                this.getOptions = t,
                this.theme = a,
                this.initOptions = i,
                this.chart = null;
            try {
                this.render(),
                    CustomEChart.instances.push(this)
            } catch (e) {
                console.error("CustomEChart: Initialization error", e)
            }
        } else
            console.warn("CustomEChart: 'selector' is required.")
    }
    render() {
        try {
            var e;
            this.selector instanceof HTMLElement ? this.element = this.selector : this.element = document.querySelector(this.selector),
            this.chart && this.chart.dispose(),
                this.element ? (e = this.getOptions(),
                    this.chart = echarts.init(this.element, this.theme, this.initOptions),
                    this.chart.setOption(e),
                    window.addEventListener("resize", debounce( () => {
                            this.chart.resize()
                        }
                        , 200))) : console.warn(`CustomEChart: No element found for selector '${this.selector}'.`)
        } catch (e) {
            console.error(`CustomEChart: Render error for '${this.selector}'`, e)
        }
    }
    static reSizeAll() {
        if (0 < CustomEChart.instances.length)
            for (let e of CustomEChart.instances)
                e.element && null !== e.element.offsetParent && requestAnimationFrame( () => {
                        e.chart.on("finished", () => {
                                requestAnimationFrame( () => {
                                        e.chart.resize()
                                    }
                                )
                            }
                        )
                    }
                )
    }
    static rerenderAll() {
        if (0 < CustomEChart.instances.length)
            for (var e of CustomEChart.instances)
                e.render()
    }
}
CustomApexChart.instances = [],
    CustomEChart.instances = [];
let themeObserver = new MutationObserver( () => {
        CustomApexChart.rerenderAll(),
            CustomEChart.rerenderAll()
    }
)
    , menuObserver = (themeObserver.observe(document.documentElement, {
    attributes: !0,
    attributeFilter: ["data-skin", "data-bs-theme"]
}),
    new MutationObserver( () => {
            requestAnimationFrame( () => {
                    CustomEChart.reSizeAll()
                }
            )
        }
    ));
menuObserver.observe(document.documentElement, {
    attributes: !0,
    attributeFilter: ["data-sidenav-size"]
});


/**
 * 图片加载失败处理（防止重复替换）
 * @param {string} defaultImage - 默认图片URL
 */
function handleImageFallback(defaultImage) {
    if (!defaultImage) {
        console.error('必须提供默认图片URL');
        return;
    }

    // 处理单个图片
    const processImage = (img) => {
        // 如果已经处理过或者已经是默认图片，则跳过
        if (img.dataset.fallbackProcessed || img.src === defaultImage) {
            return;
        }

        // 标记为已处理
        img.dataset.fallbackProcessed = 'true';

        // 保存原始src
        img.dataset.originalSrc = img.src;

        // 添加错误事件监听（只触发一次）
        const errorHandler = () => {
            // 移除事件监听，防止重复触发
            img.removeEventListener('error', errorHandler);

            // 替换为默认图片
            img.src = defaultImage;
        };

        img.addEventListener('error', errorHandler);

        // 立即检查可能已经失败的图片
        if (img.complete && img.naturalHeight === 0) {
            errorHandler();
        }
    };

    // 初始化处理所有现有图片
    const initExistingImages = () => {
        document.querySelectorAll('img').forEach(processImage);
    };

    // 监听动态添加的图片
    const setupObserver = () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeName === 'IMG') {
                        processImage(node);
                    } else if (node.querySelectorAll) {
                        node.querySelectorAll('img').forEach(processImage);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        return observer;
    };

    // 主初始化
    initExistingImages();
    return setupObserver();
}
// 监听图片加载失败时使用默认图片
handleImageFallback('/static/images/system/load_error.jpg');
