import { ImgHTMLAttributes } from 'react';

/** The YA monogram from the brand logo. */
export default function AppLogoIcon({ className, ...props }: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <>
            <img src="/images/brand/monogram.png" alt="" aria-hidden className={`dark:hidden ${className ?? ''}`} {...props} />
            <img src="/images/brand/monogram-light.png" alt="" aria-hidden className={`hidden dark:block ${className ?? ''}`} {...props} />
        </>
    );
}
