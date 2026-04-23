@props([
  'modalId' => 'activate-modal',
  'openButtonId' => null,
  'redirectTo' => null,
  'inviteCode' => '',
  'autoOpen' => false,
  'closeRedirectTo' => null,
  'title' => null,
  'closeLabel' => null,
  'description' => null,
  'pinLabel' => null,
  'pinConfirmLabel' => null,
  'pinAriaLabel' => null,
  'pinConfirmAriaLabel' => null,
  'submitLabel' => null,
  'loginUrl' => null,
  'loginLabel' => null,
  'mismatchError' => null,
  'incompleteError' => null,
])

@php
  $closeButtonId = $modalId.'-close';
  $titleId = $modalId.'-title';
  $errorId = $modalId.'-error';
  $pinFormId = $modalId.'-form';
  $passwordInputId = $modalId.'-pin';
  $passwordConfirmationInputId = $modalId.'-pin-confirmation';
  $passwordHiddenInputId = $modalId.'-password';
  $passwordConfirmationHiddenInputId = $modalId.'-password-confirmation';
  $submitButtonId = $modalId.'-submit';
  $switchToLoginButtonId = $modalId.'-switch-to-login';
  $loginModePasswordButtonId = $modalId.'-login-mode-password';
  $loginModeMnemonicButtonId = $modalId.'-login-mode-mnemonic';
  $loginPanelPasswordInputId = $modalId.'-login-username';
  $loginPanelPasswordSecretInputId = $modalId.'-login-password';
  $loginPanelMnemonicInputId = $modalId.'-login-mnemonic';
  $title = is_string($title) && $title !== '' ? $title : __('pages/me.activate_modal.title');
  $closeLabel = is_string($closeLabel) && $closeLabel !== '' ? $closeLabel : __('pages/me.activate_modal.close');
  $description = is_string($description) && $description !== '' ? $description : __('pages/me.activate_modal.description');
  $pinLabel = is_string($pinLabel) && $pinLabel !== '' ? $pinLabel : __('pages/me.activate_modal.pin_label');
  $pinConfirmLabel = is_string($pinConfirmLabel) && $pinConfirmLabel !== '' ? $pinConfirmLabel : __('pages/me.activate_modal.pin_confirm_label');
  $pinAriaLabel = is_string($pinAriaLabel) && $pinAriaLabel !== '' ? $pinAriaLabel : __('pages/me.activate_modal.pin_aria');
  $pinConfirmAriaLabel = is_string($pinConfirmAriaLabel) && $pinConfirmAriaLabel !== '' ? $pinConfirmAriaLabel : __('pages/me.activate_modal.pin_confirm_aria');
  $submitLabel = is_string($submitLabel) && $submitLabel !== '' ? $submitLabel : __('pages/me.activate_modal.submit');
  $loginUrl = is_string($loginUrl) && $loginUrl !== '' ? $loginUrl : null;
  $loginLabel = is_string($loginLabel) && $loginLabel !== '' ? $loginLabel : __('pages/me.activate_modal.login');
  $mismatchError = is_string($mismatchError) && $mismatchError !== '' ? $mismatchError : __('pages/me.activate_modal.mismatch_error');
  $incompleteError = is_string($incompleteError) && $incompleteError !== '' ? $incompleteError : __('pages/me.activate_modal.incomplete_error');
  $loginRedirectTo = null;
  if ($loginUrl !== null) {
    $loginQuery = parse_url($loginUrl, PHP_URL_QUERY);
    if (is_string($loginQuery)) {
      parse_str($loginQuery, $loginQueryValues);
      if (isset($loginQueryValues['redirect_to']) && is_string($loginQueryValues['redirect_to']) && $loginQueryValues['redirect_to'] !== '') {
        $loginRedirectTo = $loginQueryValues['redirect_to'];
      }
    }
  }
  $hasLoginErrors = $errors->has('username') || $errors->has('mnemonic_phrase');
  $activePanel = $hasLoginErrors ? 'login' : 'pin';
  $activeLoginMode = $errors->has('mnemonic_phrase') ? 'mnemonic' : 'password';
  $pinPanelTitle = $title;
  $passwordLoginTitle = __('pages/me.activate_modal.switch_password_login');
  $mnemonicLoginTitle = __('pages/me.activate_modal.switch_mnemonic_login');
@endphp

<dialog id="{{ $modalId }}" class="theme-modal theme-pin-modal">
  <div class="p-5 md:p-6">
    <div class="mb-5 flex items-center justify-between">
      <h2 id="{{ $titleId }}" class="text-scale-title font-semibold">{{ $title }}</h2>
      <button id="{{ $closeButtonId }}" class="rounded-lg px-2.5 py-1.5 text-theme-secondary hover:bg-theme-secondary/60">{{ $closeLabel }}</button>
    </div>

    <div data-auth-panel="pin" @class(['hidden' => $loginUrl !== null && $activePanel !== 'pin'])>
    <form id="{{ $pinFormId }}" method="POST" action="/register" autocomplete="off" class="space-y-4">
      @csrf
      @if (is_string($redirectTo) && $redirectTo !== '')
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
      @endif
      <input type="hidden" name="invite_code" value="{{ $inviteCode }}">
      <input id="{{ $passwordHiddenInputId }}" type="hidden" name="password" value="">
      <input id="{{ $passwordConfirmationHiddenInputId }}" type="hidden" name="password_confirmation" value="">

      <p class="text-scale-body text-theme-secondary">{{ $description }}</p>

      <div>
        <label for="{{ $passwordInputId }}" class="mb-1.5 block text-scale-body">{{ $pinLabel }}</label>
        <div class="relative">
          <input
            id="{{ $passwordInputId }}"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="6"
            required
            autocomplete="off"
            autocapitalize="off"
            autocorrect="off"
            spellcheck="false"
            data-1p-ignore="true"
            data-lpignore="true"
            class="absolute inset-0 z-10 h-full w-full cursor-text opacity-0"
            aria-label="{{ $pinAriaLabel }}"
          >
          <div class="grid grid-cols-6 gap-2 rounded-xl border border-theme bg-theme-secondary px-2.5 py-3">
            @for ($index = 0; $index < 6; $index++)
              <span
                data-pin-slot="primary"
                data-pin-slot-index="{{ $index }}"
                class="flex h-10 items-center justify-center rounded-lg border border-theme bg-theme-secondary text-2xl font-bold text-theme"
              ></span>
            @endfor
          </div>
        </div>
      </div>

      <div>
        <label for="{{ $passwordConfirmationInputId }}" class="mb-1.5 block text-scale-body">{{ $pinConfirmLabel }}</label>
        <div class="relative">
          <input
            id="{{ $passwordConfirmationInputId }}"
            type="text"
            inputmode="numeric"
            pattern="[0-9]*"
            maxlength="6"
            required
            autocomplete="off"
            autocapitalize="off"
            autocorrect="off"
            spellcheck="false"
            data-1p-ignore="true"
            data-lpignore="true"
            class="absolute inset-0 z-10 h-full w-full cursor-text opacity-0"
            aria-label="{{ $pinConfirmAriaLabel }}"
          >
          <div class="grid grid-cols-6 gap-2 rounded-xl border border-theme bg-theme-secondary px-2.5 py-3">
            @for ($index = 0; $index < 6; $index++)
              <span
                data-pin-slot="confirm"
                data-pin-slot-index="{{ $index }}"
                class="flex h-10 items-center justify-center rounded-lg border border-theme bg-theme-secondary text-2xl font-bold text-theme"
              ></span>
            @endfor
          </div>
        </div>
      </div>

      <p id="{{ $errorId }}" class="mt-4 min-h-[1.25rem] text-scale-body text-[rgb(var(--theme-rose))]">
        @error('password'){{ $message }}@enderror
      </p>

      <button
        id="{{ $submitButtonId }}"
        type="submit"
        class="w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 font-semibold text-theme-on-primary"
      >
        {{ $submitLabel }}
      </button>

    </form>
    @if ($loginUrl !== null)
      <button
        id="{{ $switchToLoginButtonId }}"
        type="button"
        data-switch-panel="login"
        class="mt-4 block w-full text-center text-scale-body text-theme-secondary underline decoration-theme underline-offset-2 hover:text-theme"
      >
        {{ $loginLabel }}
      </button>
    @endif
    </div>

    @if ($loginUrl !== null)
      <section data-auth-panel="login" @class(['hidden' => $activePanel !== 'login'])>
          <div class="mb-4 grid grid-cols-2 rounded-xl border border-theme bg-theme-secondary p-1">
            <button id="{{ $loginModePasswordButtonId }}" type="button" data-login-mode-trigger="password" class="rounded-lg px-2 py-2 text-scale-ui font-semibold transition">
              {{ __('pages/me.activate_modal.switch_password_login') }}
            </button>
            <button id="{{ $loginModeMnemonicButtonId }}" type="button" data-login-mode-trigger="mnemonic" class="rounded-lg px-2 py-2 text-scale-ui font-semibold transition">
              {{ __('pages/me.activate_modal.switch_mnemonic_login') }}
            </button>
          </div>

        <div data-login-mode-panel="password" @class(['hidden' => $activeLoginMode !== 'password'])>
          <form method="POST" action="/login" class="space-y-4">
            @csrf
            @if ($loginRedirectTo !== null)
              <input type="hidden" name="redirect_to" value="{{ $loginRedirectTo }}">
            @endif
            <div>
              <label class="mb-1 block text-scale-body" for="{{ $loginPanelPasswordInputId }}">{{ __('pages/login.username_label') }}</label>
              <input id="{{ $loginPanelPasswordInputId }}" name="username" value="{{ old('username') }}" class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5" required>
              @error('username')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <div>
              <label class="mb-1 block text-scale-body" for="{{ $loginPanelPasswordSecretInputId }}">{{ __('pages/login.password_label') }}</label>
              <input id="{{ $loginPanelPasswordSecretInputId }}" type="password" name="password" class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5" required>
            </div>

            <label class="flex items-center gap-2 text-scale-body">
              <input type="checkbox" name="remember" value="1" class="rounded border-theme bg-theme-secondary" @checked(old('remember') === '1')>
              {{ __('pages/login.remember_me') }}
            </label>

            <button class="w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 text-scale-ui font-semibold text-theme-on-primary">
              {{ __('pages/login.submit') }}
            </button>
          </form>
        </div>

        <div data-login-mode-panel="mnemonic" @class(['hidden' => $activeLoginMode !== 'mnemonic'])>
          <form method="POST" action="/login/mnemonic" class="space-y-4">
            @csrf
            @if ($loginRedirectTo !== null)
              <input type="hidden" name="redirect_to" value="{{ $loginRedirectTo }}">
            @endif
            <div>
              <label class="mb-1 block text-scale-body" for="{{ $loginPanelMnemonicInputId }}">{{ __('pages/login.mnemonic_label') }}</label>
              <textarea
                id="{{ $loginPanelMnemonicInputId }}"
                name="mnemonic_phrase"
                rows="3"
                class="w-full rounded-xl border border-theme bg-theme-secondary px-3 py-2.5"
                placeholder="{{ __('pages/login.mnemonic_placeholder') }}"
                required
              >{{ old('mnemonic_phrase') }}</textarea>
              @error('mnemonic_phrase')<p class="mt-1 text-scale-body text-[rgb(var(--theme-rose))]">{{ $message }}</p>@enderror
            </div>

            <p class="-mt-1 text-scale-body text-theme-secondary">{{ __('pages/login.mnemonic_security_tip') }}</p>

            <label class="flex items-center gap-2 text-scale-body">
              <input type="checkbox" name="remember" value="1" class="rounded border-theme bg-theme-secondary" @checked(old('remember') === '1')>
              {{ __('pages/login.remember_me') }}
            </label>

            <button class="w-full rounded-lg bg-[rgb(var(--theme-primary))] px-4 py-2.5 text-scale-ui font-semibold text-theme-on-primary">
              {{ __('pages/login.mnemonic_submit') }}
            </button>
          </form>
        </div>
      </section>
    @endif
  </div>
</dialog>

<script>
  (() => {
    const modal = document.getElementById(@json($modalId));
    const openButtonId = @json($openButtonId);
    const shouldAutoOpen = @json((bool) $autoOpen);
    const closeRedirectUrl = @json(is_string($closeRedirectTo) && $closeRedirectTo !== '' ? $closeRedirectTo : null);
    const openBtn = openButtonId ? document.getElementById(openButtonId) : null;
    const closeBtn = document.getElementById(@json($closeButtonId));
    const panelSwitchButtons = modal ? Array.from(modal.querySelectorAll('[data-switch-panel]')) : [];
    const rootPanels = modal ? Array.from(modal.querySelectorAll('[data-auth-panel]')) : [];
    const loginModeButtons = modal ? Array.from(modal.querySelectorAll('[data-login-mode-trigger]')) : [];
    const loginModePanels = modal ? Array.from(modal.querySelectorAll('[data-login-mode-panel]')) : [];
    const loginPasswordInput = document.getElementById(@json($loginPanelPasswordInputId));
    const loginPasswordSecretInput = document.getElementById(@json($loginPanelPasswordSecretInputId));
    const loginMnemonicInput = document.getElementById(@json($loginPanelMnemonicInputId));
    const titleNode = document.getElementById(@json($titleId));
    const errorNode = document.getElementById(@json($errorId));
    const form = document.getElementById(@json($pinFormId));
    const passwordInput = document.getElementById(@json($passwordInputId));
    const confirmationInput = document.getElementById(@json($passwordConfirmationInputId));
    const passwordHiddenInput = document.getElementById(@json($passwordHiddenInputId));
    const confirmationHiddenInput = document.getElementById(@json($passwordConfirmationHiddenInputId));
    const submitButton = document.getElementById(@json($submitButtonId));
    const primarySlots = modal ? Array.from(modal.querySelectorAll('[data-pin-slot="primary"]')) : [];
    const confirmSlots = modal ? Array.from(modal.querySelectorAll('[data-pin-slot="confirm"]')) : [];
    const activeLoginModeClasses = ['bg-[rgb(var(--theme-primary))]', 'text-theme-on-primary'];
    const inactiveLoginModeClasses = ['text-theme-secondary'];
    let activeField = 'primary';

    if (!modal || !form || !passwordInput || !confirmationInput || !passwordHiddenInput || !confirmationHiddenInput || !submitButton) {
      return;
    }

    const setDialogTitle = (panelName, loginMode = 'password') => {
      if (!titleNode) {
        return;
      }
      if (panelName === 'pin') {
        titleNode.textContent = @json($pinPanelTitle);
        return;
      }
      if (loginMode === 'mnemonic') {
        titleNode.textContent = @json($mnemonicLoginTitle);
        return;
      }
      titleNode.textContent = @json($passwordLoginTitle);
    };

    const setRootPanel = (panelName) => {
      rootPanels.forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.authPanel !== panelName);
      });
    };

    const setLoginMode = (mode) => {
      loginModeButtons.forEach((button) => {
        const isActive = button.dataset.loginModeTrigger === mode;
        activeLoginModeClasses.forEach((className) => button.classList.toggle(className, isActive));
        inactiveLoginModeClasses.forEach((className) => button.classList.toggle(className, !isActive));
      });
      loginModePanels.forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.loginModePanel !== mode);
      });
    };

    const renderSlots = (slots, value, isActive) => {
      const activeIndex = value.length >= 6 ? 5 : value.length;

      slots.forEach((slot, index) => {
        const filled = index < value.length;
        const showActive = isActive && index === activeIndex;

        slot.textContent = filled ? '●' : '';
        slot.classList.toggle('border-[rgb(var(--theme-primary))]', showActive);
        slot.classList.toggle('text-[rgb(var(--theme-primary))]', showActive);
        slot.classList.toggle('ring-2', showActive);
        slot.classList.toggle('ring-[rgb(var(--theme-primary))]/35', showActive);
        slot.classList.toggle('border-theme', !showActive);
        slot.classList.toggle('text-theme', !showActive);
        slot.classList.toggle('ring-0', !showActive);
      });
    };

    const renderAllSlots = () => {
      renderSlots(primarySlots, passwordInput.value, activeField === 'primary');
      renderSlots(confirmSlots, confirmationInput.value, activeField === 'confirm');
    };

    const resetState = () => {
      if (errorNode) {
        errorNode.textContent = '';
      }
      passwordInput.value = '';
      confirmationInput.value = '';
      passwordHiddenInput.value = '';
      confirmationHiddenInput.value = '';
      submitButton.removeAttribute('disabled');
      submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
      activeField = 'primary';
      renderAllSlots();
    };

    const sanitizePin = (value) => value.replace(/\D/g, '').slice(0, 6);

    const syncInput = (input) => {
      input.value = sanitizePin(input.value);
    };

    const updateSubmitState = () => {
      const valid = passwordInput.value.length === 6 && confirmationInput.value.length === 6;
      submitButton.toggleAttribute('disabled', !valid);
      submitButton.classList.toggle('opacity-60', !valid);
      submitButton.classList.toggle('cursor-not-allowed', !valid);
    };

    const focusPinInput = () => {
      activeField = 'primary';
      passwordInput.focus();
      passwordInput.setSelectionRange(passwordInput.value.length, passwordInput.value.length);
      renderAllSlots();
    };

    const focusConfirmationInput = () => {
      activeField = 'confirm';
      confirmationInput.focus();
      confirmationInput.setSelectionRange(confirmationInput.value.length, confirmationInput.value.length);
      renderAllSlots();
    };

    const moveToConfirmationWhenPrimaryComplete = () => {
      if (passwordInput.value.length < 6 || document.activeElement === confirmationInput) {
        return;
      }

      focusConfirmationInput();
      window.setTimeout(() => {
        if (document.activeElement !== confirmationInput) {
          focusConfirmationInput();
        }
      }, 30);
    };

    const closeModal = () => {
      if (closeRedirectUrl) {
        window.location.assign(closeRedirectUrl);
        return;
      }
      modal.close();
    };

    const adjustDialogForKeyboard = () => {
      const viewport = window.visualViewport;
      if (!viewport || !modal.open) {
        return;
      }

      const focusedOnInput =
        document.activeElement === passwordInput ||
        document.activeElement === confirmationInput ||
        document.activeElement === loginPasswordInput ||
        document.activeElement === loginPasswordSecretInput ||
        document.activeElement === loginMnemonicInput;
      if (!focusedOnInput) {
        modal.style.removeProperty('transform');
        return;
      }

      const keyboardHeight = Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop);
      if (keyboardHeight <= 0) {
        modal.style.removeProperty('transform');
        return;
      }

      const lift = Math.min(Math.round(keyboardHeight * 0.58), 260);
      modal.style.transform = `translateY(-${lift}px)`;
    };

    const showMismatchError = () => {
      if (errorNode) {
        errorNode.textContent = @json($mismatchError);
      }
      confirmationInput.value = '';
      updateSubmitState();
      focusConfirmationInput();
    };

    openBtn?.addEventListener('click', () => {
      modal.showModal();
      resetState();
      setRootPanel('pin');
      setLoginMode('password');
      setDialogTitle('pin');
      focusPinInput();
    });

    panelSwitchButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const panelName = button.dataset.switchPanel;
        if (panelName !== 'login') {
          return;
        }
        setRootPanel('login');
        setLoginMode('password');
        setDialogTitle('login', 'password');
        loginPasswordInput?.focus();
      });
    });

    loginModeButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const mode = button.dataset.loginModeTrigger;
        if (!mode) {
          return;
        }
        setLoginMode(mode);
        setDialogTitle('login', mode);
        if (mode === 'mnemonic') {
          loginMnemonicInput?.focus();
          return;
        }
        loginPasswordInput?.focus();
      });
    });

    closeBtn?.addEventListener('click', closeModal);
    passwordInput.addEventListener('input', () => {
      syncInput(passwordInput);
      if (errorNode) {
        errorNode.textContent = '';
      }
      activeField = 'primary';
      renderAllSlots();
      moveToConfirmationWhenPrimaryComplete();
      updateSubmitState();
    });
    passwordInput.addEventListener('change', moveToConfirmationWhenPrimaryComplete);
    passwordInput.addEventListener('paste', () => {
      window.setTimeout(() => {
        syncInput(passwordInput);
        activeField = 'primary';
        renderAllSlots();
        moveToConfirmationWhenPrimaryComplete();
        updateSubmitState();
      }, 0);
    });
    passwordInput.addEventListener('focus', () => {
      activeField = 'primary';
      passwordInput.setSelectionRange(passwordInput.value.length, passwordInput.value.length);
      renderAllSlots();
      adjustDialogForKeyboard();
    });
    confirmationInput.addEventListener('input', () => {
      syncInput(confirmationInput);
      if (errorNode) {
        errorNode.textContent = '';
      }
      activeField = 'confirm';
      renderAllSlots();
      updateSubmitState();
    });
    confirmationInput.addEventListener('focus', () => {
      activeField = 'confirm';
      confirmationInput.setSelectionRange(confirmationInput.value.length, confirmationInput.value.length);
      renderAllSlots();
      adjustDialogForKeyboard();
    });
    passwordInput.addEventListener('blur', () => {
      window.setTimeout(adjustDialogForKeyboard, 30);
    });
    confirmationInput.addEventListener('blur', () => {
      window.setTimeout(adjustDialogForKeyboard, 30);
    });
    form.addEventListener('submit', (event) => {
      syncInput(passwordInput);
      syncInput(confirmationInput);
      renderAllSlots();

      if (passwordInput.value.length !== 6 || confirmationInput.value.length !== 6) {
        event.preventDefault();
        if (errorNode) {
          errorNode.textContent = @json($incompleteError);
        }
        updateSubmitState();
        return;
      }

      if (passwordInput.value !== confirmationInput.value) {
        event.preventDefault();
        showMismatchError();
        return;
      }

      passwordHiddenInput.value = passwordInput.value;
      confirmationHiddenInput.value = confirmationInput.value;
      updateSubmitState();
    });

    modal.addEventListener('click', (event) => {
      const rect = modal.getBoundingClientRect();
      const isInside =
        event.clientX >= rect.left &&
        event.clientX <= rect.right &&
        event.clientY >= rect.top &&
        event.clientY <= rect.bottom;

      if (!isInside) {
        closeModal();
      }
    });

    modal.addEventListener('close', resetState);
    modal.addEventListener('close', () => {
      modal.style.removeProperty('transform');
    });

    const applyServerInitialState = () => {
      updateSubmitState();
      if (@json($activePanel) === 'login') {
        setRootPanel('login');
        setLoginMode(@json($activeLoginMode));
        setDialogTitle('login', @json($activeLoginMode));
        if (@json($activeLoginMode) === 'mnemonic') {
          loginMnemonicInput?.focus();
        } else {
          (loginPasswordInput ?? loginPasswordSecretInput)?.focus();
        }
      } else {
        setRootPanel('pin');
        setLoginMode('password');
        setDialogTitle('pin');
        focusPinInput();
      }
      adjustDialogForKeyboard();
    };

    const observer = new MutationObserver(() => {
      if (!modal.open) {
        return;
      }
      applyServerInitialState();
    });

    observer.observe(modal, {
      attributes: true,
      attributeFilter: ['open'],
    });

    window.visualViewport?.addEventListener('resize', adjustDialogForKeyboard);
    window.visualViewport?.addEventListener('scroll', adjustDialogForKeyboard);

    @if ($errors->has('password') || $hasLoginErrors)
      modal.showModal();
      applyServerInitialState();
    @endif

    if (shouldAutoOpen && !modal.open) {
      modal.showModal();
      updateSubmitState();
      setRootPanel('pin');
      setLoginMode('password');
      setDialogTitle('pin');
      focusPinInput();
    } else if (!modal.open) {
      setRootPanel(@json($activePanel));
      setLoginMode(@json($activeLoginMode));
      setDialogTitle(@json($activePanel), @json($activeLoginMode));
    }
  })();
</script>
