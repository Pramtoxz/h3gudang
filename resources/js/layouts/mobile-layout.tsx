import { type PropsWithChildren } from 'react';

/**
 * Wrapper layout untuk layar lapangan (HP landscape di lengan).
 * Full-width tanpa sidebar, optimal untuk viewport ~873×393px.
 */
export default function MobileLayoutWrapper({ children }: PropsWithChildren) {
    return <>{children}</>;
}
