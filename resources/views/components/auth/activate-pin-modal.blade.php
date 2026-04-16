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
  $passwordInputId = $modalId.'-password';
  $passwordConfirmationInputId = $modalId.'-password-confirmation';
  $submitButtonId = $modalId.'-submit';
@endphp

<dialog id="{{ $modalId }}" class="theme-modal theme-pin-modal">
  <div class="p-5 md:p-6">
    <div class="mb-5 flex items-center justify-between">
      <h2 class="text-scale-title font-semibold">设置 6 位数字密码</h2>
      <button id="{{ $closeButtonId }}" class="rounded-lg px-2.5 py-1.5 text-theme-secondary hover:bg-theme-secondary/60">关闭</button>
    </div>

    <form id="{{ $pinFormId }}" method="POST" action="/register" class="space-y-4">
      @csrf
      @if (is_string($redirectTo) && $redirectTo !== '')
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
      @endif
      <input type="hidden" name="invite_code" value="{{ $inviteCode }}">

      <p class="text-scale-body text-theme-secondary">请输入并确认 6 位数字密码</p>

      <div>
        <label for="{{ $passwordInputId }}" class="mb-1.5 block text-scale-body">输入 6 位 PIN</label>
        <input
          id="{{ $passwordInputId }}"
          type="password"
          name="password"
          inputmode="numeric"
          pattern="\d{6}"
          minlength="6"
          maxlength="6"
          autocomplete="new-password"
          required
          class="w-full rounded-xl border border-theme bg-theme-secondary px-4 py-3 text-center text-xl tracking-[0.45em] text-theme"
        >
      </div>

      <div>
        <label for="{{ $passwordConfirmationInputId }}" class="mb-1.5 block text-scale-body">确认 6 位 PIN</label>
        <input
          id="{{ $passwordConfirmationInputId }}"
          type="password"
          name="password_confirmation"
          inputmode="numeric"
          pattern="\d{6}"
          minlength="6"
          maxlength="6"
          autocomplete="new-password"
          required
          class="w-full rounded-xl border border-theme bg-theme-secondary px-4 py-3 text-center text-xl tracking-[0.45em] text-theme"
        >
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
    const submitButton = document.getElementById(@json($submitButtonId));

    if (!modal || !form || !passwordInput || !confirmationInput || !submitButton) {
      return;
    }

    const resetState = () => {
      if (errorNode) {
        errorNode.textContent = '';
      }
      submitButton.removeAttribute('disabled');
      submitButton.classList.remove('opacity-60', 'cursor-not-allowed');
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
      passwordInput.focus();
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
      confirmationInput.focus();
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
      updateSubmitState();
    });
    confirmationInput.addEventListener('input', () => {
      syncInput(confirmationInput);
      if (errorNode) {
        errorNode.textContent = '';
      }
      updateSubmitState();
    });
    form.addEventListener('submit', (event) => {
      syncInput(passwordInput);
      syncInput(confirmationInput);
      if (passwordInput.value !== confirmationInput.value) {
        event.preventDefault();
        showMismatchError();
        return;
      }
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
