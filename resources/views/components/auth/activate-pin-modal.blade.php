@props([
  'modalId' => 'activate-modal',
  'openButtonId' => null,
  'redirectTo' => null,
  'inviteCode' => '',
])

@php
  $closeButtonId = $modalId.'-close';
  $statusId = $modalId.'-status';
  $errorId = $modalId.'-error';
  $pinInputId = $modalId.'-pin-input';
  $pinStageInputId = $modalId.'-pin-stage';
  $pinFormId = $modalId.'-form';
  $passwordInputId = $modalId.'-password-value';
  $passwordConfirmationInputId = $modalId.'-password-confirmation-value';
@endphp

<dialog id="{{ $modalId }}" class="theme-modal theme-pin-modal">
  <div class="flex h-full flex-col p-5 md:p-6">
    <div class="mb-6 flex items-center justify-between">
      <h2 class="text-scale-title font-semibold">设置 6 位数字密码</h2>
      <button id="{{ $closeButtonId }}" class="rounded-lg px-2.5 py-1.5 text-theme-secondary hover:bg-theme-secondary/60">关闭</button>
    </div>

    <form id="{{ $pinFormId }}" method="POST" action="/register" class="flex h-full flex-col">
      @csrf
      @if (is_string($redirectTo) && $redirectTo !== '')
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
      @endif
      <input type="hidden" name="invite_code" value="{{ $inviteCode }}">
      <input id="{{ $passwordInputId }}" type="hidden" name="password" value="">
      <input id="{{ $passwordConfirmationInputId }}" type="hidden" name="password_confirmation" value="">

      <p id="{{ $statusId }}" class="text-scale-body text-theme-secondary">请输入 6 位数字密码</p>
      <div id="{{ $pinStageInputId }}" class="mt-3 text-scale-micro font-medium uppercase tracking-[0.18em] text-[rgb(var(--theme-primary))]">SET PASSWORD</div>

      <button type="button" id="{{ $modalId }}-pin-area" class="mt-5 rounded-2xl border border-theme bg-theme-secondary/40 px-3 py-5">
        <div class="grid grid-cols-6 gap-2.5">
          @for ($index = 0; $index < 6; $index++)
            <span data-pin-slot="{{ $index }}" class="flex h-12 items-center justify-center rounded-lg border border-theme bg-theme-secondary text-xl font-semibold text-theme"></span>
          @endfor
        </div>
      </button>

      <input
        id="{{ $pinInputId }}"
        type="password"
        inputmode="numeric"
        autocomplete="one-time-code"
        maxlength="6"
        class="pointer-events-none absolute -left-[9999px] top-0 h-10 w-10 opacity-0"
        aria-label="输入6位数字密码"
      >

      <p id="{{ $errorId }}" class="mt-4 min-h-[1.25rem] text-scale-body text-[rgb(var(--theme-rose))]">
        @error('password'){{ $message }}@enderror
      </p>

      <div class="mt-auto pt-6">
        <button type="button" id="{{ $modalId }}-reset" class="w-full rounded-lg border border-theme px-4 py-2.5 text-scale-body text-theme-secondary hover:bg-theme-secondary/30">重置输入</button>
      </div>
    </form>
  </div>
</dialog>

<script>
  (() => {
    const modal = document.getElementById(@json($modalId));
    const openButtonId = @json($openButtonId);
    const openBtn = openButtonId ? document.getElementById(openButtonId) : null;
    const closeBtn = document.getElementById(@json($closeButtonId));
    const statusNode = document.getElementById(@json($statusId));
    const stepNode = document.getElementById(@json($pinStageInputId));
    const errorNode = document.getElementById(@json($errorId));
    const pinInput = document.getElementById(@json($pinInputId));
    const pinArea = document.getElementById(@json($modalId.'-pin-area'));
    const pinSlots = modal ? Array.from(modal.querySelectorAll('[data-pin-slot]')) : [];
    const resetButton = document.getElementById(@json($modalId.'-reset'));
    const form = document.getElementById(@json($pinFormId));
    const passwordInput = document.getElementById(@json($passwordInputId));
    const confirmationInput = document.getElementById(@json($passwordConfirmationInputId));

    if (!modal || !form || !pinInput || !passwordInput || !confirmationInput) {
      return;
    }

    let step = 'setup';
    let setupPin = '';
    let confirmPin = '';

    const currentValue = () => (step === 'setup' ? setupPin : confirmPin);

    const updateStageText = () => {
      if (step === 'setup') {
        statusNode.textContent = '请输入 6 位数字密码';
        stepNode.textContent = 'SET PASSWORD';
        return;
      }

      statusNode.textContent = '请再次输入 6 位数字密码';
      stepNode.textContent = 'CONFIRM PASSWORD';
    };

    const renderSlots = () => {
      const activeValue = currentValue();
      pinSlots.forEach((slot, index) => {
        slot.textContent = index < activeValue.length ? '•' : '';
      });
    };

    const resetState = () => {
      step = 'setup';
      setupPin = '';
      confirmPin = '';
      pinInput.value = '';
      passwordInput.value = '';
      confirmationInput.value = '';
      if (errorNode) {
        errorNode.textContent = '';
      }
      updateStageText();
      renderSlots();
    };

    const focusPinInput = () => {
      window.setTimeout(() => pinInput.focus(), 0);
    };

    const closeModal = () => {
      modal.close();
    };

    const moveToConfirmStep = () => {
      step = 'confirm';
      confirmPin = '';
      pinInput.value = '';
      updateStageText();
      renderSlots();
      focusPinInput();
    };

    const submitWhenConfirmed = () => {
      if (setupPin !== confirmPin) {
        if (errorNode) {
          errorNode.textContent = '两次密码不一致，请重新输入';
        }
        confirmPin = '';
        pinInput.value = '';
        renderSlots();
        focusPinInput();
        return;
      }

      passwordInput.value = setupPin;
      confirmationInput.value = confirmPin;
      form.submit();
    };

    const syncInputValue = (value) => {
      const digits = value.replace(/\D/g, '').slice(0, 6);

      if (step === 'setup') {
        setupPin = digits;
      } else {
        confirmPin = digits;
      }

      pinInput.value = digits;
      if (errorNode) {
        errorNode.textContent = '';
      }

      renderSlots();

      if (digits.length !== 6) {
        return;
      }

      if (step === 'setup') {
        moveToConfirmStep();
        return;
      }

      submitWhenConfirmed();
    };

    openBtn?.addEventListener('click', () => {
      modal.showModal();
      resetState();
      focusPinInput();
    });

    closeBtn?.addEventListener('click', closeModal);
    resetButton?.addEventListener('click', () => {
      resetState();
      focusPinInput();
    });
    pinArea?.addEventListener('click', focusPinInput);
    pinInput.addEventListener('input', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLInputElement)) {
        return;
      }
      syncInputValue(target.value);
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
      renderSlots();
      updateStageText();
    });

    observer.observe(modal, {
      attributes: true,
      attributeFilter: ['open'],
    });

    @if ($errors->has('password'))
      modal.showModal();
      updateStageText();
      renderSlots();
      focusPinInput();
    @endif
  })();
</script>
