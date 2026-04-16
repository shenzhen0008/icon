@props([
  'modalId' => 'activate-modal',
  'openButtonId' => null,
  'redirectTo' => null,
  'inviteCode' => '',
])

@php
  $closeButtonId = $modalId.'-close';
  $errorId = $modalId.'-error';
  $pinFormId = $modalId.'-form';
  $passwordInputId = $modalId.'-pin';
  $passwordConfirmationInputId = $modalId.'-pin-confirmation';
  $passwordHiddenInputId = $modalId.'-password';
  $passwordConfirmationHiddenInputId = $modalId.'-password-confirmation';
  $submitButtonId = $modalId.'-submit';
@endphp

<dialog id="{{ $modalId }}" class="theme-modal theme-pin-modal">
  <div class="p-5 md:p-6">
    <div class="mb-5 flex items-center justify-between">
      <h2 class="text-scale-title font-semibold">设置 6 位数字密码</h2>
      <button id="{{ $closeButtonId }}" class="rounded-lg px-2.5 py-1.5 text-theme-secondary hover:bg-theme-secondary/60">关闭</button>
    </div>

    <form id="{{ $pinFormId }}" method="POST" action="/register" autocomplete="off" class="space-y-4">
      @csrf
      @if (is_string($redirectTo) && $redirectTo !== '')
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
      @endif
      <input type="hidden" name="invite_code" value="{{ $inviteCode }}">
      <input id="{{ $passwordHiddenInputId }}" type="hidden" name="password" value="">
      <input id="{{ $passwordConfirmationHiddenInputId }}" type="hidden" name="password_confirmation" value="">

      <p class="text-scale-body text-theme-secondary">请输入并确认 6 位数字密码</p>

      <div>
        <label for="{{ $passwordInputId }}" class="mb-1.5 block text-scale-body">输入 6 位 PIN</label>
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
            aria-label="输入6位PIN"
          >
          <div class="grid grid-cols-6 gap-2 rounded-xl border border-theme bg-theme-secondary px-2.5 py-3">
            @for ($index = 0; $index < 6; $index++)
              <span
                data-pin-slot="primary"
                data-pin-slot-index="{{ $index }}"
                class="flex h-10 items-center justify-center rounded-lg border border-theme bg-theme-secondary text-lg font-semibold text-theme"
              ></span>
            @endfor
          </div>
        </div>
      </div>

      <div>
        <label for="{{ $passwordConfirmationInputId }}" class="mb-1.5 block text-scale-body">确认 6 位 PIN</label>
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
            aria-label="确认6位PIN"
          >
          <div class="grid grid-cols-6 gap-2 rounded-xl border border-theme bg-theme-secondary px-2.5 py-3">
            @for ($index = 0; $index < 6; $index++)
              <span
                data-pin-slot="confirm"
                data-pin-slot-index="{{ $index }}"
                class="flex h-10 items-center justify-center rounded-lg border border-theme bg-theme-secondary text-lg font-semibold text-theme"
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
        确认注册
      </button>
    </form>
  </div>
</dialog>

<script>
  (() => {
    const modal = document.getElementById(@json($modalId));
    const openButtonId = @json($openButtonId);
    const openBtn = openButtonId ? document.getElementById(openButtonId) : null;
    const closeBtn = document.getElementById(@json($closeButtonId));
    const errorNode = document.getElementById(@json($errorId));
    const form = document.getElementById(@json($pinFormId));
    const passwordInput = document.getElementById(@json($passwordInputId));
    const confirmationInput = document.getElementById(@json($passwordConfirmationInputId));
    const passwordHiddenInput = document.getElementById(@json($passwordHiddenInputId));
    const confirmationHiddenInput = document.getElementById(@json($passwordConfirmationHiddenInputId));
    const submitButton = document.getElementById(@json($submitButtonId));
    const primarySlots = modal ? Array.from(modal.querySelectorAll('[data-pin-slot="primary"]')) : [];
    const confirmSlots = modal ? Array.from(modal.querySelectorAll('[data-pin-slot="confirm"]')) : [];

    if (!modal || !form || !passwordInput || !confirmationInput || !passwordHiddenInput || !confirmationHiddenInput || !submitButton) {
      return;
    }

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
      renderSlots(primarySlots, '');
      renderSlots(confirmSlots, '');
    };

    const sanitizePin = (value) => value.replace(/\D/g, '').slice(0, 6);

    const syncInput = (input) => {
      input.value = sanitizePin(input.value);
    };

    const renderSlots = (slots, value) => {
      slots.forEach((slot, index) => {
        slot.textContent = index < value.length ? '•' : '';
      });
    };

    const updateSubmitState = () => {
      const valid = passwordInput.value.length === 6 && confirmationInput.value.length === 6;
      submitButton.toggleAttribute('disabled', !valid);
      submitButton.classList.toggle('opacity-60', !valid);
      submitButton.classList.toggle('cursor-not-allowed', !valid);
    };

    const focusPinInput = () => {
      passwordInput.focus();
    };

    const focusConfirmationInput = () => {
      confirmationInput.focus();
      confirmationInput.setSelectionRange(confirmationInput.value.length, confirmationInput.value.length);
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
      modal.close();
    };

    const showMismatchError = () => {
      if (errorNode) {
        errorNode.textContent = '两次 PIN 不一致，请重新确认';
      }
      confirmationInput.value = '';
      updateSubmitState();
      focusConfirmationInput();
    };

    openBtn?.addEventListener('click', () => {
      modal.showModal();
      resetState();
      focusPinInput();
    });

    closeBtn?.addEventListener('click', closeModal);
    passwordInput.addEventListener('input', () => {
      syncInput(passwordInput);
      if (errorNode) {
        errorNode.textContent = '';
      }
      renderSlots(primarySlots, passwordInput.value);
      moveToConfirmationWhenPrimaryComplete();
      updateSubmitState();
    });
    passwordInput.addEventListener('change', moveToConfirmationWhenPrimaryComplete);
    passwordInput.addEventListener('paste', () => {
      window.setTimeout(() => {
        syncInput(passwordInput);
        renderSlots(primarySlots, passwordInput.value);
        moveToConfirmationWhenPrimaryComplete();
        updateSubmitState();
      }, 0);
    });
    confirmationInput.addEventListener('input', () => {
      syncInput(confirmationInput);
      if (errorNode) {
        errorNode.textContent = '';
      }
      renderSlots(confirmSlots, confirmationInput.value);
      updateSubmitState();
    });
    form.addEventListener('submit', (event) => {
      syncInput(passwordInput);
      syncInput(confirmationInput);
      renderSlots(primarySlots, passwordInput.value);
      renderSlots(confirmSlots, confirmationInput.value);

      if (passwordInput.value.length !== 6 || confirmationInput.value.length !== 6) {
        event.preventDefault();
        if (errorNode) {
          errorNode.textContent = '请输入完整的 6 位 PIN';
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

    const observer = new MutationObserver(() => {
      if (!modal.open) {
        return;
      }

      focusPinInput();
      updateSubmitState();
    });

    observer.observe(modal, {
      attributes: true,
      attributeFilter: ['open'],
    });

    @if ($errors->has('password'))
      modal.showModal();
      updateSubmitState();
      focusPinInput();
    @endif
  })();
</script>
