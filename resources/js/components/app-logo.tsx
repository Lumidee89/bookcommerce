import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-white shadow-sm ring-1 ring-black/5 dark:bg-transparent dark:ring-white/10">
                <AppLogoIcon className="size-7 object-contain" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="text-brand-900 dark:text-gold-200 mb-0.5 truncate font-serif text-base leading-none font-semibold tracking-wider uppercase">
                    Yemi Adebanjo
                </span>
                <span className="text-muted-foreground truncate text-[0.7rem] leading-none">Dashboard</span>
            </div>
        </>
    );
}
