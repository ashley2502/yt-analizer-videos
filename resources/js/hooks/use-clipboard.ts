// Credit: https://usehooks-ts.com/ — with insecure-context fallback for local HTTP (.test)
import { useState } from 'react';

export type CopiedValue = string | null;
export type CopyFn = (text: string) => Promise<boolean>;
export type UseClipboardReturn = [CopiedValue, CopyFn];

function legacyCopy(text: string): boolean {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    try {
        return document.execCommand('copy');
    } catch {
        return false;
    } finally {
        document.body.removeChild(textarea);
    }
}

export async function copyText(text: string): Promise<boolean> {
    if (typeof window === 'undefined') {
        return false;
    }

    if (navigator?.clipboard?.writeText && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch {
            // fall through to legacy
        }
    }

    return legacyCopy(text);
}

export function useClipboard(): UseClipboardReturn {
    const [copiedText, setCopiedText] = useState<CopiedValue>(null);

    const copy: CopyFn = async (text) => {
        const ok = await copyText(text);

        if (ok) {
            setCopiedText(text);

            return true;
        }

        console.warn('Copy failed');
        setCopiedText(null);

        return false;
    };

    return [copiedText, copy];
}
