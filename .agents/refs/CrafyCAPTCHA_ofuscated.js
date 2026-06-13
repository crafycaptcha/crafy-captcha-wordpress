import nacl from 'tweetnacl';
import naclUtil from 'tweetnacl-util';

const CONFIG = {
  iframeUrl: 'https://captcha.crafy.net/challenge/',
  turnstileScript: 'https://challenges.cloudflare.com/turnstile/v0/api.js'
};

const DICTIONARY = {
  es: { verify_human: "Verifica que eres humano", new_challenge: "Nuevo Desaf\u00edo", connection_error: "Error de conexi\u00f3n. Recarga la p\u00e1gina.", session_expired: "Sesi\u00f3n expirada. Recarga la p\u00e1gina." },
  en: { verify_human: "Verify that you are human", new_challenge: "New Challenge", connection_error: "Connection error. Please reload the page.", session_expired: "Session expired. Please reload the page." },
  fr: { verify_human: "V\u00e9rifiez que vous \u00eates humain", new_challenge: "Nouveau d\u00e9fi", connection_error: "Erreur de connexion. Veuillez recharger la page.", session_expired: "Session expir\u00e9e. Veuillez recharger la page." },
  pt: { verify_human: "Verifique se voc\u00ea \u00e9 humano", new_challenge: "Novo Desafio", connection_error: "Erro de conex\u00e3o. Por favor, recarregue a p\u00e1gina.", session_expired: "Sess\u00e3o expirada. Por favor, recarregue a p\u00e1gina." },
  de: { verify_human: "Best\u00e4tigen Sie, dass Sie ein Mensch sind", new_challenge: "Neue Herausforderung", connection_error: "Verbindungsfehler. Bitte laden Sie die Seite neu.", session_expired: "Sitzung abgelaufen. Bitte laden Sie die Seite neu." },
  it: { verify_human: "Verifica di essere umano", new_challenge: "Nuova sfida", connection_error: "Errore di connessione. Ricarica la pagina.", session_expired: "Sessione scaduta. Ricarica la pagina." },
  ru: { verify_human: "\u041f\u043e\u0434\u0442\u0432\u0435\u0440\u0434\u0438\u0442\u0435, \u0447\u0442\u043e \u0432\u044b \u0447\u0435\u043b\u043e\u0432\u0435\u043a", new_challenge: "\u041d\u043e\u0432\u043e\u0435 \u0438\u0441\u043f\u044b\u0442\u0430\u043d\u0438\u0435", connection_error: "\u041e\u0448\u0438\u0431\u043a\u0430 \u043f\u043e\u0434\u043a\u043b\u044e\u0447\u0435\u043d\u0438\u044f. \u041f\u043e\u0436\u0430\u043b\u0443\u0439\u0441\u0442\u0430, \u043f\u0435\u0440\u0435\u0437\u0430\u0433\u0440\u0443\u0437\u0438\u0442\u0435 \u0441\u0442\u0440\u0430\u043d\u0438\u0446\u0443.", session_expired: "\u0421\u0435\u0441\u0441\u0438\u044f \u0438\u0441\u0442\u0435\u043a\u043b\u0430. \u041f\u043e\u0436\u0430\u043b\u0443\u0439\u0441\u0442\u0430, \u043f\u0435\u0440\u0435\u0437\u0430\u0433\u0440\u0443\u0437\u0438\u0442\u0435 \u0441\u0442\u0440\u0430\u043d\u0438\u0446\u0443." },
  zh: { verify_human: "\u9a8c\u8bc1\u60a8\u662f\u4eba\u7c7b", new_challenge: "\u65b0\u6311\u6218", connection_error: "\u8fde\u63a5\u9519\u8bef\u3002\u8bf7\u91cd\u65b0\u52a0\u8f7d\u9875\u9762\u3002", session_expired: "\u4f1a\u8bdd\u5df2\u8fc7\u671f\u3002\u8bf7\u91cd\u65b0\u52a0\u8f7d\u9875\u9762\u3002" },
  ja: { verify_human: "\u4eba\u9593\u3067\u3042\u308b\u3053\u3068\u3092\u78ba\u8a8d\u3057\u3066\u304f\u3060\u3055\u3044", new_challenge: "\u65b0\u3057\u3044\u30c1\u30e3\u30ec\u30f3\u30b8", connection_error: "\u63a5\u7d9a\u30a8\u30e9\u30fc\u3002\u30da\u30fc\u30b8\u3092\u518d\u8aad\u307f\u8fbc\u307f\u3057\u3066\u304f\u3060\u3055\u3044\u3002", session_expired: "\u30bb\u30c3\u30b7\u30e7\u30f3\u306e\u6709\u52b9\u671f\u9650\u304c\u5207\u308c\u307e\u3057\u305f\u3002\u30da\u30fc\u30b8\u3092\u518d\u8aad\u307f\u8fbc\u307f\u3057\u3066\u304f\u3060\u3055\u3044\u3002" },
  hi: { verify_human: "\u0938\u0924\u094d\u092f\u093e\u092a\u093f\u0924 \u0915\u0930\u0947\u0902 \u0915\u093f \u0906\u092a \u092e\u093e\u0928\u0935 \u0939\u0948\u0902", new_challenge: "\u0928\u0908 \u091a\u0941\u0928\u094c\u0924\u0940", connection_error: "\u0915\u0928\u0947\u0915\u094d\u0936\u0928 \u0924\u094d\u0930\u0941\u091f\u093f\u0964 \u0915\u0943\u092a\u092f\u093e \u092a\u0943\u0937\u094d\u0920 \u0915\u094b \u092a\u0941\u0928\u0903 \u0932\u094b\u0921 \u0915\u0930\u0947\u0902\u0964", session_expired: "\u0938\u0924\u094d\u0930 \u0938\u092e\u093e\u092a\u094d\u0924 \u0939\u094b \u0917\u092f\u093e\u0964 \u0915\u0943\u092a\u092f\u093e \u092a\u0943\u0937\u094d\u0920 \u0915\u094b \u092a\u0941\u0928\u0903 \u0932\u094b\u0921 \u0915\u0930\u0947\u0902\u0964" }
};

function utf8ToBase64(str) {
  const bytes = new TextEncoder().encode(str);
  const binString = String.fromCodePoint(...bytes);
  return btoa(binString);
}

// ------------------------------------------
// O-S-N-F ----------------------------------


// ------------------------------------------
// ------------------------------------------


class CrafyCAPTCHA {
  constructor() {
    this.autoLoad = true;
    this._iframeLoadRequested = false;
    this._iframeSrcSet = false;
    this.publicKey = null;
    this.publicToken = null;
    this.signingKey = null;
    this.encryptedOptions = null;
    this.container = null;
    this.iframe = null;
    this.startWidget = null;
    this.footerControl = null;
    this.turnstileWidgetId = null;
    this.flowToken = null;
    this._cachedTurnstileSiteKey = undefined;
    this._turnstileStatus = 'pending'; // 'pending' | 'solved' | 'error'
    this._turnstileToken = null;
    this._turnstileInitReceived = false;
    this.iframeUrl = CONFIG.iframeUrl;
    this.debug = false;
    this.isSolved = false;
    this.lang = 'es';
    this.computedStyles = {};
    this.shadowRoot = null;

    const rawLang = (typeof navigator !== 'undefined' && navigator.languages && navigator.languages.length)
      ? navigator.languages[0]
      : (typeof navigator !== 'undefined' ? (navigator.language || navigator.userLanguage) : 'es');
    const langIso2 = rawLang.split(/[-_]/)[0].toLowerCase();
    if (langIso2.length) {
      this.lang = langIso2;
    }
  }

  setAutoLoad(value) {
    this.autoLoad = !!value;
  }

  loadIframe() {
    if (this.autoLoad) return;
    if (this._iframeLoadRequested) return;
    this._iframeLoadRequested = true;

    if (this._triggerPreload) {
      this._triggerPreload();
    }
  }

  setDebug(value) {
    this.debug = !!value;
  }

  _log(...args) {
    if (this.debug) console.log('[CrafyCAPTCHA JS SDK]', ...args);
  }

  _warn(...args) {
    if (this.debug) console.warn('[CrafyCAPTCHA JS SDK]', ...args);
  }

  _error(...args) {
    if (this.debug) console.error('[CrafyCAPTCHA JS SDK]', ...args);
  }

  async init(containerRef, publicKey, publicToken, signingPublicKey, options = {}, internalOptions = {}) {
    // 1. Ceder el hilo de ejecución momentáneamente (Yield) 
    // Evita bloquear la renderización inicial del DOM de la página del cliente
    await new Promise(resolve => setTimeout(resolve, 0));

    this.publicKey = publicKey;
    this.publicToken = publicToken;
    this.signingKey = signingPublicKey;
    this.options = options;
    this.internalOptions = internalOptions || {};

    await this._fetchOptions();
    this._startUnifiedExpirationTimer();

    this.computedStyles = this._resolveStyles(options.theme, options.style);
    if (this.options.iframeUrl) this.iframeUrl = this.options.iframeUrl;
    // Normalizar: asegurar barra final para evitar 301 de Apache que convierte POST→GET
    if (!this.iframeUrl.endsWith('/')) this.iframeUrl += '/';

    this.container = typeof containerRef === 'string'
      ? document.getElementById(containerRef)
      : containerRef;

    if (!this.container) return;

    if (this.container.hasAttribute('data-crafy-initialized')) {
      this._warn('El widget ya está inicializado en este contenedor.');
      return;
    }
    this.container.setAttribute('data-crafy-initialized', 'true');

    this.shadowRoot = this.container.attachShadow({ mode: 'closed' });

    this._injectStyles();
    this._renderInterface();

    if (this._cachedTurnstileSiteKey === undefined) {
      this._fetchTurnstileSiteKey();
    } else if (this._cachedTurnstileSiteKey !== null) {
      this._loadTurnstile().then(() => {
        this._renderTurnstile(this._cachedTurnstileSiteKey);
      }).catch(e => this._error(e));
    }

    if (typeof window !== 'undefined') {
      this._messageHandler = this._handleMessage.bind(this);
      window.addEventListener('message', this._messageHandler);
      // Start loading Turnstile in the background to speed up subsequent renders
      this._loadTurnstile().catch(() => { });

      this._onlineHandler = () => this._recoverNetwork();
      this._offlineHandler = () => this._showOfflineWarning();
      window.addEventListener('online', this._onlineHandler);
      window.addEventListener('offline', this._offlineHandler);
    }

    this.parentForm = this.container.closest('form');
    if (this.parentForm) {
      this.parentForm.addEventListener('submit', (e) => {
        if (!this.isSolved) {
          e.preventDefault();
          if (this._showCaptchaUI) this._showCaptchaUI();
          this._pendingSubmit = true;
        }
      });
    }
  }

  _startUnifiedExpirationTimer() {
    if (this._expireTimer) clearTimeout(this._expireTimer);
    // 19 minutos de expiración unificada (1140000 ms)
    this._expireTimer = setTimeout(() => {
      this._handleExpiration();
    }, 19 * 60 * 1000);
  }

  async _handleExpiration() {
    this._log('Tiempo de expiración unificada alcanzado.');

    // Limpiamos el token del form si estuviera resuelto
    const inputName = this.options.inputName || 'CrafyCAPTCHA_token';
    const input = document.querySelector(`input[name="${inputName}"]`);
    if (input) input.value = '';
    this.isSolved = false;

    if (this.options.optionsUrl) {
      this._log('Refrescando eo expirado vía optionsUrl...');
      await this._fetchOptions();
      this.reset();
      this._startUnifiedExpirationTimer();
      if (this.options.onExpire) this.options.onExpire();
    } else {
      this._log('Sin optionsUrl. Bloqueando por expiración.');
      this._showExpiredUI(true);
      if (this.options.onExpire) this.options.onExpire();
    }
  }

  async _fetchOptions() {
    if (this.options.optionsUrl) {
      try {
        let payload = Object.assign({}, this.internalOptions.fetchOptionsParameters || {});
        if ('action' in payload) {
          this._warn("El parámetro 'action' está reservado en fetchOptionsParameters y será sobrescrito.");
        }
        payload.action = 'get_options';

        const response = await fetch(this.options.optionsUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data && data.eo) {
          this.encryptedOptions = data.eo;
        } else {
          this.encryptedOptions = this.options.encryptedOptions || '';
        }
      } catch (err) {
        this._error('Error fetching optionsUrl', err);
        this.encryptedOptions = this.options.encryptedOptions || '';
      }
    } else {
      this.encryptedOptions = this.options.encryptedOptions || '';
    }
  }

  destroy() {
    if (typeof window !== 'undefined' && this._messageHandler) {
      window.removeEventListener('message', this._messageHandler);
      window.removeEventListener('online', this._onlineHandler);
      window.removeEventListener('offline', this._offlineHandler);
    }
    if (this._turnstileRetryInterval) {
      clearInterval(this._turnstileRetryInterval);
      this._turnstileRetryInterval = null;
    }
    if (this._tamperObserver) {
      this._tamperObserver.disconnect();
      this._tamperObserver = null;
    }
    if (this._expireTimer) {
      clearTimeout(this._expireTimer);
      this._expireTimer = null;
    }
    if (this.container) {
      this.container.removeAttribute('data-crafy-initialized');
      if (this.shadowRoot) {
        this.shadowRoot.innerHTML = '';
      }
      this.container.innerHTML = '';
    }
  }

  _translate(text) {
    if (this.lang && DICTIONARY[this.lang] && DICTIONARY[this.lang][text]) {
      return DICTIONARY[this.lang][text];
    }
    return DICTIONARY['es'][text] || text;
  }

  _resolveStyles(theme = 'light', customStyle = {}) {
    const isDark = theme === 'dark';
    const defaults = {
      bg: isDark ? '#1f2937' : '#f9fafb',
      bgHover: isDark ? '#374151' : '#f3f4f6',
      text: isDark ? '#e5e7eb' : '#374151',
      border: isDark ? '#4b5563' : '#d1d5db',
      primary: '#2563eb',
      footerText: isDark ? '#9ca3af' : '#6b7280'
    };
    return {
      bg: customStyle.background || defaults.bg,
      bgHover: customStyle.backgroundHover || defaults.bgHover,
      text: customStyle.color || defaults.text,
      border: customStyle.borderColor || defaults.border,
      primary: customStyle.primary || defaults.primary,
      footerText: customStyle.footerColor || defaults.footerText,
      theme: theme
    };
  }

  _injectStyles() {
    if (typeof document === 'undefined') return;

    const s = this.computedStyles;
    const css = `
      .crafy-wrapper { all: initial; display: block; width: 100%; height: 100%; }
      iframe { border: none; margin: 0; padding: 0; }
      .crafy-start-box { margin: auto; display: flex; align-items: center; justify-content: space-between; background: ${s.bg}; border: 1px solid ${s.border}; border-radius: 6px; padding: 12px 16px; cursor: pointer; font-family: -apple-system, system-ui, sans-serif; transition: all 0.2s ease; user-select: none; max-width: 350px; }
      .crafy-start-box:hover { background: ${s.bgHover}; border-color: ${s.text}; }
      .crafy-content { display: flex; align-items: center; gap: 12px; }
      .crafy-checkbox { width: 24px; height: 24px; border: 2px solid ${s.border}; border-radius: 4px; background: ${s.theme === 'dark' ? '#374151' : 'white'}; }
      .crafy-text { font-size: 14px; color: ${s.text}; font-weight: 500; }
      .crafy-logo { width: 20px; opacity: 0.5; filter: ${s.theme === 'dark' ? 'invert(1)' : 'none'}; }
      .crafy-footer { margin: auto; display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 350px; font-family: -apple-system, system-ui, sans-serif; font-size: 15px; color: ${s.footerText}; margin-top: 5px; }
      .crafy-reload-btn { background: none; border: none; color: ${s.primary}; cursor: pointer; padding: 4px; display: flex; align-items: center; gap: 4px; font-size: 15px; font-weight: 500; }
      .crafy-reload-btn:hover { text-decoration: underline; opacity: 0.8; }
      .crafy-reload-icon { font-size: 14px; line-height: 1; }
      .crafy-link { color: ${s.primary}; text-decoration: none; }
      .crafy-link:hover { text-decoration: underline; }
      .crafy-loading { border-color: transparent !important; border-top-color: ${s.primary} !important; border-radius: 50% !important; animation: crafy-spin 1s linear infinite; }
      @keyframes crafy-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
      .crafy-disabled { opacity: 0.7; cursor: not-allowed !important; }
      .crafy-iframe-overlay { position: relative; display: none; align-items: center; justify-content: center; width: 100%; min-height: 420px; max-width: 350px; margin: auto; background: ${s.bg}; border: 1px solid ${s.border}; border-radius: 6px; z-index: 1001; }
      .crafy-iframe-overlay .crafy-spinner { width: 36px; height: 36px; border: 3px solid ${s.border}; border-top-color: ${s.primary}; border-radius: 50%; animation: crafy-spin 0.8s linear infinite; }
    `;
    const style = document.createElement('style');
    style.id = 'crafy-styles';
    style.appendChild(document.createTextNode(css));

    const oldStyle = this.shadowRoot.querySelector('#crafy-styles');
    if (oldStyle) oldStyle.remove();
    this.shadowRoot.appendChild(style);
  }

  _getEncryptedTelemetry() {
    return new Promise((resolve) => {
      const handler = (e) => {
        window.removeEventListener('CC_REQ_RR_BE', handler);
        this._log('CCREQ times:', e.detail.detection_duration, e.detail.final_duration);
        resolve(e.detail.data);
      };
      window.addEventListener('CC_REQ_RR_BE', handler);
      window.dispatchEvent(new CustomEvent('CC_REQ_DD_BE', {
        detail: { eo: this.encryptedOptions }
      }));
    });
  }

  _renderInterface() {
    if (typeof document === 'undefined') return;

    const children = Array.from(this.shadowRoot.childNodes);
    children.forEach(child => {
      if (child.id !== 'crafy-styles') {
        this.shadowRoot.removeChild(child);
      }
    });

    const fragment = document.createDocumentFragment();

    const wrapper = document.createElement('div');
    wrapper.className = 'crafy-wrapper';

    // 1. Widget UI (Síncrono para que se vea rápido)
    this.startWidget = document.createElement('div');
    this.startWidget.className = 'crafy-start-box' + (this.autoLoad ? ' crafy-disabled' : '');
    this.startWidget.innerHTML = `<div class="crafy-content"><div class="crafy-checkbox${this.autoLoad ? ' crafy-loading' : ''}"></div><span class="crafy-text">${this._translate('verify_human')}</span></div><div class="crafy-logo">\u{1f6e1}\ufe0f</div>`;

    // 2. Iframe (Estructura base, SIN src inicialmente)
    const iframeName = 'crafy_iframe_' + Math.random().toString(36).substring(2, 15) + Date.now();
    this.iframe = document.createElement('iframe');
    this.iframe.name = iframeName;
    // No usar loading='lazy': el iframe está off-screen y el navegador nunca lo cargaría
    this.iframe.sandbox = "allow-scripts allow-same-origin allow-popups allow-forms";
    this.iframe.title = this._translate('verify_human');
    this.iframe.setAttribute('aria-label', 'CrafyCAPTCHA Security Check');
    this.iframe.setAttribute('aria-hidden', 'true');
    this.iframe.tabIndex = -1;
    this.iframe.style.cssText = `
      position: absolute !important;
      top: -9999px !important;
      left: -9999px !important;
      width: 1px !important;
      height: 1px !important;
      visibility: visible !important;
      opacity: 1 !important;
      pointer-events: none !important;
      z-index: -2147483648 !important;
      border: none !important;
    `;

    // Overlay spinner (se muestra mientras carga el iframe)
    this.iframeOverlay = document.createElement('div');
    this.iframeOverlay.className = 'crafy-iframe-overlay';
    this.iframeOverlay.innerHTML = '<div class="crafy-spinner"></div>';

    // Footer UI
    this.footerControl = document.createElement('div');
    this.footerControl.className = 'crafy-footer';
    this.footerControl.style.visibility = 'hidden';
    this.footerControl.innerHTML = `<span>Protected by <a href="https://captcha.crafy.net/" target="_blank" rel="noopener noreferrer" class="crafy-link">CrafyCAPTCHA</a></span><button type="button" class="crafy-reload-btn"><span class="crafy-reload-icon">\u21bb</span> ${this._translate('new_challenge')}</button>`;

    this.footerControl.querySelector('.crafy-reload-btn').addEventListener('click', (e) => {
      e.preventDefault(); e.stopPropagation(); this.reset();
    });

    // Formulario oculto para enviar datos por POST al iframe
    this.submitForm = document.createElement('form');
    this.submitForm.method = 'POST';
    this.submitForm.action = this.iframeUrl;
    this.submitForm.target = iframeName;
    this.submitForm.style.display = 'none';

    const addInput = (name, value) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      this.submitForm.appendChild(input);
      return input;
    };

    addInput('pt', this.publicToken);
    this.cdweoInput = addInput('cdweo', '');
    addInput('theme', this.computedStyles.theme);
    if (this.computedStyles.bg) addInput('bg', this.computedStyles.bg);
    if (this.computedStyles.text) addInput('text', this.computedStyles.text);
    if (this.computedStyles.primary) addInput('primary', this.computedStyles.primary);
    if (this.computedStyles.border) addInput('border', this.computedStyles.border);
    addInput('parent_origin', window.location.origin);

    // Ensamblar en memoria
    wrapper.appendChild(this.startWidget);
    wrapper.appendChild(this.iframeOverlay);
    wrapper.appendChild(this.iframe);
    wrapper.appendChild(this.footerControl);
    wrapper.appendChild(this.submitForm);

    fragment.appendChild(wrapper);

    // Inyectar TODO de un solo golpe al DOM real (1 solo reflow) dentro del shadowRoot
    this.shadowRoot.appendChild(fragment);

    this.isReady = false;
    this._iframeLoaded = false;

    const preloadIframe = async () => {
      if (this._iframeSrcSet) return;
      this._iframeSrcSet = true;

      this.iframe.addEventListener('load', () => {
        this._iframeLoaded = true;
        if (this._waitingForIframeLoad) {
          this._waitingForIframeLoad = false;
          this._revealIframe();
        }
      }, { once: true });

      const cdweo = await this._getEncryptedTelemetry();
      this.cdweoInput.value = JSON.stringify(cdweo);
      this.submitForm.submit();

      this.isReady = true;
      if (typeof this._flushMessageQueue === 'function') this._flushMessageQueue();
      this.startWidget.classList.remove('crafy-disabled');
      const checkbox = this.startWidget.querySelector('.crafy-checkbox');
      if (checkbox) checkbox.classList.remove('crafy-loading');
    };
    this._triggerPreload = preloadIframe;

    this._revealIframe = () => {
      this.iframeOverlay.style.display = 'none';
      this.iframe.style.cssText = `
        position: relative !important;
        width: 100% !important;
        height: 420px !important;
        pointer-events: auto !important;
        z-index: 1000 !important;
        border: none !important;
        overflow: hidden !important;
      `;
      this.iframe.removeAttribute('aria-hidden');
      this.iframe.tabIndex = 0;
      this.footerControl.style.visibility = 'visible';
    };

    this._showCaptchaUI = () => {
      if (this.autoLoad && !this.isReady) return;

      if (!this.autoLoad && !this._iframeLoadRequested) {
        this.loadIframe();
      }

      this.startWidget.style.display = 'none';

      // Mostrar overlay spinner mientras el iframe termina de cargar
      this.iframeOverlay.style.display = 'flex';
      this.footerControl.style.visibility = 'hidden';

      // Si el iframe ya cargó, revelar directamente
      if (this._iframeLoaded) {
        this._revealIframe();
      } else {
        this._waitingForIframeLoad = true;
      }
    };

    // Evento Click: mostrar el iframe ya pre-cargado (o forzar carga si aún no ocurrió)
    this.startWidget.addEventListener('click', this._showCaptchaUI);

    // Pre-cargar el iframe de forma no intrusiva si autoLoad es true o ya fue solicitado:
    // requestIdleCallback (con timeout de 2s) > setTimeout 500ms como fallback.
    // Esto asegura que el iframe empiece a cargar poco después del render inicial
    // de la página, sin competir con los recursos críticos del host.
    if (this.autoLoad || this._iframeLoadRequested) {
      if (typeof window !== 'undefined') {
        if (window.requestIdleCallback) {
          window.requestIdleCallback(preloadIframe, { timeout: 2000 });
        } else {
          setTimeout(preloadIframe, 500);
        }
      }
    }
  }

  async reset() {
    if (!this.iframe || !this.submitForm) return;
    if (this._isResetting) return; // Evitar clicks múltiples
    this._isResetting = true;

    this.isReady = false;
    this._iframeLoaded = false;
    this._iframeSrcSet = false;
    this._iframeLoadRequested = false;

    // Feedback visual en el startWidget (si estuviera visible)
    if (this.startWidget) {
      if (this.autoLoad) {
        this.startWidget.classList.add('crafy-disabled');
        const checkbox = this.startWidget.querySelector('.crafy-checkbox');
        if (checkbox) checkbox.classList.add('crafy-loading');
      } else {
        this.startWidget.classList.remove('crafy-disabled');
        const checkbox = this.startWidget.querySelector('.crafy-checkbox');
        if (checkbox) checkbox.classList.remove('crafy-loading');
      }
    }

    // Feedback visual inmediato en el iframe y footer (lo que el usuario ve)
    if (this.iframe) {
      this.iframe.style.pointerEvents = 'none';
      this.iframe.style.opacity = '0.5';
    }
    if (this.footerControl) {
      const reloadBtn = this.footerControl.querySelector('.crafy-reload-btn');
      if (reloadBtn) {
        reloadBtn.style.pointerEvents = 'none';
        reloadBtn.style.opacity = '0.7';
        reloadBtn.innerHTML = `<span class="crafy-loading" style="display:inline-block; border-width:2px; border-style:solid; width:12px; height:12px; margin-right:6px; border-color:transparent; border-top-color:inherit; border-radius:50%;"></span> ${this._translate('new_challenge')}`;
      }
    }

    const cdweo = await this._getEncryptedTelemetry();
    this.cdweoInput.value = JSON.stringify(cdweo);
    this.submitForm.submit();

    // Esperar a que el nuevo iframe termine de cargar antes de quitar la animación
    await new Promise((resolve) => {
      const onLoad = () => {
        this.iframe.removeEventListener('load', onLoad);
        this._iframeLoaded = true;
        if (this._waitingForIframeLoad) {
          this._waitingForIframeLoad = false;
          this._revealIframe();
        }
        resolve();
      };
      this.iframe.addEventListener('load', onLoad);
    });

    this.isReady = true;
    this._iframeSrcSet = true;
    this._iframeLoadRequested = true;

    if (this.startWidget) {
      this.startWidget.classList.remove('crafy-disabled');
      const checkbox = this.startWidget.querySelector('.crafy-checkbox');
      if (checkbox) checkbox.classList.remove('crafy-loading');
    }

    // Restaurar visualmente
    if (this.iframe) {
      this.iframe.style.pointerEvents = 'auto';
      this.iframe.style.opacity = '1';
    }
    if (this.footerControl) {
      const reloadBtn = this.footerControl.querySelector('.crafy-reload-btn');
      if (reloadBtn) {
        reloadBtn.style.pointerEvents = 'auto';
        reloadBtn.style.opacity = '1';
        reloadBtn.innerHTML = `<span class="crafy-reload-icon">\u21bb</span> ${this._translate('new_challenge')}`;
      }
    }

    this._turnstileStatus = 'pending';
    this._turnstileToken = null;
    this._turnstileInitReceived = false;
    if (typeof window !== 'undefined' && window.turnstile && this.turnstileWidgetId) {
      turnstile.reset(this.turnstileWidgetId);
    }
    this._hideTurnstileWidget();
    this._isResetting = false;
    if (typeof this._flushMessageQueue === 'function') this._flushMessageQueue();
  }

  async _fetchTurnstileSiteKey() {
    try {
      const apiUrl = this.iframeUrl.replace(/\/challenge\/?$/, '/api/turnstile_site_key.php');
      const response = await fetch(`${apiUrl}?pk=${encodeURIComponent(this.publicKey)}`);
      if (!response.ok) {
        throw new Error('Network error');
      }
      const data = await response.json();
      if (data.status === 'success') {
        if (!this._verifySignature(data.data.payload, data.data.signature)) {
          this._reportFatalError('TURNSTILE_BLOCKED', 'Ataque detectado: Firma de API de site_key inválida');
          this._showOfflineWarning();
          throw new Error('Ataque detectado: Firma inválida');
        }
        const decodedPayload = JSON.parse(data.data.payload);
        this._cachedTurnstileSiteKey = decodedPayload.payload.site_key;

        if (this._cachedTurnstileSiteKey) {
          this._loadTurnstile().then(() => {
            this._renderTurnstile(this._cachedTurnstileSiteKey);
          }).catch(e => {
            this._error('Error cargando Turnstile:', e);
          });
        } else {
          if (this._turnstileInitReceived) {
            this._turnstileStatus = 'solved';
            this._turnstileToken = 'skipped';
            this._sendToIframe('TURNSTILE_SOLVED', { token: 'skipped' });
          }
        }
      } else {
        throw new Error(data.message || 'Error fetching site key');
      }
    } catch (err) {
      if (err.message && err.message.includes('Ataque detectado')) return;
      this._error('Error fetching Turnstile site key', err);
      this._showOfflineWarning();
    }
  }

  async _handleMessage(event) {
    const expectedOrigin = new URL(this.iframeUrl).origin;
    if (event.origin !== expectedOrigin) {
      return;
    }
    if (event.source !== this.iframe.contentWindow) {
      if (event.data?.action === 'HANDSHAKE') {
        this._warn('Aceptando HANDSHAKE a pesar del desajuste de event.source (posible quirk del navegador durante reload rápido).');
      } else {
        this._warn('Mensaje ignorado porque event.source no coincide con el contentWindow del iframe.', { action: event.data?.action });
        return;
      }
    }
    const { action, payload, signature, server_sign } = event.data;

    if (action && action !== 'RESIZE') {
      this._log('Mensaje recibido del iframe:', action);
    }

    if (action === 'RESIZE' && payload?.height && this.iframe) {
      this.iframe.style.height = payload.height + 'px';
      return;
    }

    if (!action) return;

    if (action === 'HANDSHAKE') {
      this._turnstileInitReceived = true;
      this._log('Procesando HANDSHAKE...');
      setTimeout(async () => {
        if (!this._verifySignature(payload, signature)) {
          this._error('Firma inválida para HANDSHAKE.');
          return;
        }
        let decoded_payload = typeof payload === 'string' ? JSON.parse(payload).payload : (payload.payload || payload);
        this.flowToken = decoded_payload.flow_token;
        this._log('HANDSHAKE verificado. flow_token:', decoded_payload.flow_token);

        if (this._cachedTurnstileSiteKey === null) {
          this._log('Modo sin Turnstile, enviando TURNSTILE_SOLVED: skipped');
          this._turnstileStatus = 'solved';
          this._turnstileToken = 'skipped';
          this._sendToIframe('TURNSTILE_SOLVED', { token: 'skipped' });
        }
      }, 0);
    }

    if (action === 'REQUEST_TURNSTILE_STATUS') {
      this._log(`Iframe solicitó estado de Turnstile. Estado actual: ${this._turnstileStatus}`);
      if (this._turnstileStatus === 'solved') {
        this._sendToIframe('TURNSTILE_SOLVED', { token: this._turnstileToken });
      } else if (this._turnstileStatus === 'error') {
        this._sendToIframe('TURNSTILE_ERROR', { message: 'Network error' });
      } else if (this._turnstileStatus === 'pending') {
        if (!this._turnstileInitReceived) {
          this._warn('Estado es pending y no se ha recibido HANDSHAKE. Solicitando al iframe que re-envíe (REQUEST_HANDSHAKE_RETRY).');
          this._sendToIframe('REQUEST_HANDSHAKE_RETRY', {});
        } else {
          this._log('Estado es pending pero HANDSHAKE ya fue recibido. Esperando resolución de Turnstile.');
        }
      }
      return;
    }

    if (action === 'REQUEST_RELOAD') {
      this._log('Iframe solicitó recarga (puzzle fallido o sesión expirada). Ejecutando reset()...');
      this.reset();
      return;
    }

    if (action === 'CHALLENGE_COMPLETE') {
      setTimeout(() => {
        if (!this._verifySignature(payload, signature)) return;
        this._handleSuccess(payload, server_sign);
      }, 0);
    }
  }

  _verifySignature(payloadStr, signatureBase64) {
    try {
      const messageUint8 = naclUtil.decodeUTF8(payloadStr);
      const signatureUint8 = naclUtil.decodeBase64(signatureBase64);
      const publicKeyUint8 = naclUtil.decodeBase64(this.signingKey);
      return nacl.sign.detached.verify(messageUint8, signatureUint8, publicKeyUint8);
    } catch (e) {
      this._error('Error verificando firma:', e);
      return false;
    }
  }

  _loadTurnstile() {
    if (this._turnstileLoadPromise) return this._turnstileLoadPromise;

    this._turnstileLoadPromise = new Promise((resolve, reject) => {
      if (typeof window === 'undefined' || window.turnstile) return resolve();

      if (document.getElementById('crafy-turnstile-script')) {
        const checkTurnstile = setInterval(() => {
          if (window.turnstile) { clearInterval(checkTurnstile); resolve(); }
        }, 50);
        return;
      }

      const preconnect = document.createElement('link');
      preconnect.rel = 'preconnect';
      preconnect.href = 'https://challenges.cloudflare.com';
      document.head.appendChild(preconnect);

      const script = document.createElement('script');
      script.id = 'crafy-turnstile-script';
      script.src = CONFIG.turnstileScript;
      script.async = true;
      script.defer = true;
      if (this.options && this.options.cspNonce) {
        script.nonce = this.options.cspNonce;
      }

      const timeout = setTimeout(() => {
        reject(new Error('Turnstile load timeout'));
        this._showOfflineWarning();
      }, 7000);

      script.onload = () => {
        clearTimeout(timeout);
        resolve();
      };

      script.onerror = () => {
        clearTimeout(timeout);
        this._reportFatalError('TURNSTILE_BLOCKED', 'No se pudo cargar el script de Cloudflare');
        reject(new Error('Turnstile blocked by network'));
        this._showOfflineWarning();
      };

      document.head.appendChild(script);
    });

    return this._turnstileLoadPromise;
  }

  _showTurnstileWidget() {
    const tDiv = document.getElementById('crafy-turnstile-hidden');
    if (tDiv) {
      tDiv.style.cssText = `
        display: flex !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background-color: rgba(0, 0, 0, 0.6) !important;
        z-index: 2147483647 !important;
        align-items: center !important;
        justify-content: center !important;
        pointer-events: auto !important;
        visibility: visible !important;
        opacity: 1 !important;
      `;
    }
  }

  _hideTurnstileWidget() {
    const tDiv = document.getElementById('crafy-turnstile-hidden');
    if (tDiv) {
      tDiv.style.cssText = `
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
        width: 1px !important;
        height: 1px !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: none !important;
        z-index: -2147483648 !important;
      `;
    }
  }

  _renderTurnstile(siteKey) {
    if (typeof document === 'undefined') return;
    let tDiv = document.getElementById('crafy-turnstile-hidden');
    if (!tDiv) {
      tDiv = document.createElement('div');
      tDiv.id = 'crafy-turnstile-hidden';
      tDiv.style.cssText = `
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
        width: 1px !important;
        height: 1px !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: none !important;
        z-index: -2147483648 !important;
      `;
      tDiv.addEventListener('click', (e) => {
        if (e.target === tDiv) this._hideTurnstileWidget();
      });
      document.body.appendChild(tDiv);
    }

    // Si ya existe el widget, simplemente lo reseteamos para que lance un nuevo desafío.
    if (this.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
      turnstile.reset(this.turnstileWidgetId);
      return;
    }

    try {
      this.turnstileWidgetId = turnstile.render('#crafy-turnstile-hidden', {
        sitekey: siteKey,
        appearance: 'interaction-only',
        callback: (token) => {
          this._hideTurnstileWidget();
          this._turnstileStatus = 'solved';
          this._turnstileToken = token;
          this._sendToIframe('TURNSTILE_SOLVED', { token });
        },
        'error-callback': () => {
          this._hideTurnstileWidget();
          this._warn('Error Turnstile');
        },
        'before-interactive-callback': () => this._showTurnstileWidget(),
        'after-interactive-callback': () => this._hideTurnstileWidget(),
        'unsupported-callback': () => this._hideTurnstileWidget()
      });
    } catch (e) {
      if (typeof turnstile !== 'undefined') turnstile.reset('#crafy-turnstile-hidden');
    }
  }

  _sendToIframe(action, data) {
    if (!this._messageQueue) this._messageQueue = [];
    const msg = { action, ...data };

    if (this._isResetting || !this.isReady) {
      this._log(`Encolando ${action} porque el widget se está reseteando o no está listo...`);
      this._messageQueue.push(msg);
      return;
    }

    if (this.iframe && this.iframe.contentWindow) {
      this._log(`Enviando ${action} al iframe...`);
      let targetOrigin;
      try {
        targetOrigin = new URL(this.iframeUrl).origin;
      } catch (e) {
        this._warn('Fallo al resolver el origin del iframe. Se cancela envío por seguridad.');
        return;
      }
      this.iframe.contentWindow.postMessage(msg, targetOrigin);
    } else {
      this._warn(`Intento de enviar ${action} fallido: iframe o contentWindow no existen.`);
    }
  }

  _flushMessageQueue() {
    if (this._messageQueue && this._messageQueue.length > 0) {
      this._log(`Procesando ${this._messageQueue.length} mensajes encolados...`);
      while (this._messageQueue.length > 0) {
        const msg = this._messageQueue.shift();
        const data = { ...msg };
        delete data.action;
        this._sendToIframe(msg.action, data);
      }
    }
  }

  _handleSuccess(payload, server_sign) {
    if (typeof document === 'undefined') return;
    this.isSolved = true;
    if (this.footerControl) {
      this.footerControl.style.visibility = 'hidden';
      setTimeout(() => this.footerControl.style.visibility = 'visible', 10000);
    }

    const inputName = this.options.inputName || 'CrafyCAPTCHA_token';
    let input = document.querySelector(`input[name="${inputName}"]`);
    if (!input) {
      input = document.createElement('input');
      input.type = 'hidden';
      input.name = inputName;
      (this.container.closest('form') || this.container).appendChild(input);
    }

    const payload_for_server_str = utf8ToBase64(JSON.stringify({ payload, server_sign }));
    input.value = payload_for_server_str;
    this.container.dispatchEvent(new CustomEvent('crafy:success', { detail: payload_for_server_str }));
    if (this.options.onSuccess) this.options.onSuccess(payload_for_server_str);

    this._protectTokenInput(input, payload_for_server_str);

    if (this._pendingSubmit && this.parentForm) {
      if (typeof this.parentForm.requestSubmit === 'function') {
        this.parentForm.requestSubmit();
      } else {
        this.parentForm.submit();
      }
    }
  }

  _protectTokenInput(inputElement, validToken) {
    if (this._tamperObserver) this._tamperObserver.disconnect();

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
          if (inputElement.value !== validToken && inputElement.value !== '') {
            this._warn('Intento de manipulaciÃ³n detectado.');
            inputElement.value = validToken;
          }
        }
        if (mutation.type === 'childList' && Array.from(mutation.removedNodes).includes(inputElement)) {
          const parent = this.parentForm || this.container;
          if (parent) parent.appendChild(inputElement);
        }
      });
    });

    observer.observe(inputElement, { attributes: true });
    const parent = this.parentForm || this.container;
    if (parent) observer.observe(parent, { childList: true });

    this._tamperObserver = observer;
  }

  _showExpiredUI(permanent = false) {
    this.reset();
    if (this.startWidget) {
      this.startWidget.style.display = 'flex';
      this.startWidget.style.borderColor = 'red';
      const textSpan = this.startWidget.querySelector('.crafy-text');
      if (textSpan) {
        textSpan.style.color = 'red';
        if (permanent) {
          textSpan.innerText = this._translate('session_expired');
        }
      }
      if (permanent) {
        this.startWidget.style.pointerEvents = 'none';
        this.startWidget.classList.add('crafy-disabled');
        const checkbox = this.startWidget.querySelector('.crafy-checkbox');
        if (checkbox) {
          checkbox.classList.remove('crafy-loading');
          checkbox.style.borderColor = 'red';
          checkbox.innerHTML = '<span style="color:red; margin-left:6px; font-weight:bold; line-height:20px;">\u2715</span>';
        }
      }
    }
    if (this.iframe) {
      this.iframe.style.cssText = `
        position: absolute !important;
        top: -9999px !important;
        left: -9999px !important;
        width: 1px !important;
        height: 1px !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: none !important;
        z-index: -2147483648 !important;
        border: none !important;
      `;
      this.iframe.setAttribute('aria-hidden', 'true');
      this.iframe.tabIndex = -1;
    }
    if (this.footerControl) {
      this.footerControl.style.visibility = 'hidden';
    }
  }

  _showOfflineWarning() {
    if (this.startWidget) {
      const textSpan = this.startWidget.querySelector('.crafy-text');
      if (textSpan) {
        this._originalText = textSpan.innerText;
        textSpan.innerText = this._translate('connection_error');
        textSpan.style.color = 'orange';
      }
    }
  }

  _recoverNetwork() {
    if (this.startWidget) {
      const textSpan = this.startWidget.querySelector('.crafy-text');
      if (textSpan && this._originalText) {
        textSpan.innerText = this._originalText;
        textSpan.style.color = this.computedStyles.text;
      }
    }
    if (!window.turnstile) {
      this._turnstileLoadPromise = null;
      this._loadTurnstile().catch(() => { });
    }
  }

  _reportFatalError(errorType, message) {
    if (typeof navigator !== 'undefined' && navigator.sendBeacon) {
      const payload = JSON.stringify({
        pk: this.publicKey,
        error: errorType,
        msg: message,
        url: window.location.hostname
      });
      navigator.sendBeacon('https://captcha.crafy.net/api/telemetry.php?code=crafy_telemetry_secure_2026', payload);
    }
  }
}

// Soporte para Singleton
CrafyCAPTCHA._instance = null;
CrafyCAPTCHA.init = function (containerRef, publicKey, publicToken, signingPublicKey, options = {}, internalOptions = {}) {
  if (CrafyCAPTCHA._instance) {
    if (CrafyCAPTCHA._instance.publicKey && CrafyCAPTCHA._instance.publicKey !== publicKey) {
      console.warn('[CrafyCAPTCHA JS SDK] Warning: CrafyCAPTCHA already initialized with different credentials. Ignoring new init call.');
      return CrafyCAPTCHA._instance;
    }
  } else {
    CrafyCAPTCHA._instance = new CrafyCAPTCHA();
  }
  CrafyCAPTCHA._instance.init(containerRef, publicKey, publicToken, signingPublicKey, options, internalOptions);
  return CrafyCAPTCHA._instance;
};
CrafyCAPTCHA.setDebug = function (value) {
  if (!CrafyCAPTCHA._instance) CrafyCAPTCHA._instance = new CrafyCAPTCHA();
  CrafyCAPTCHA._instance.setDebug(value);
};
CrafyCAPTCHA.setAutoLoad = function (value) {
  if (!CrafyCAPTCHA._instance) CrafyCAPTCHA._instance = new CrafyCAPTCHA();
  CrafyCAPTCHA._instance.setAutoLoad(value);
};
CrafyCAPTCHA.loadIframe = function () {
  if (CrafyCAPTCHA._instance) return CrafyCAPTCHA._instance.loadIframe();
};
CrafyCAPTCHA.reset = function () {
  if (CrafyCAPTCHA._instance) return CrafyCAPTCHA._instance.reset();
};

// Exponemos la clase de manera global para que se pueda llamar con etiquetas script en el navegador
if (typeof window !== 'undefined') {
  window.CrafyCAPTCHA = CrafyCAPTCHA;
  window.dispatchEvent(new CustomEvent('CrafyCAPTCHALoaded', { detail: { CrafyCAPTCHA } }));
}

export default CrafyCAPTCHA;